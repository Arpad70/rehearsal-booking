<?php  
namespace App\Http\Controllers;  
use App\Http\Requests\CreateReservationRequest;  
use App\Models\Reservation;  
use App\Jobs\TurnOnShellyJob;  
use App\Jobs\TurnOffShellyJob;  
use Illuminate\Support\Facades\Mail;  
use App\Mail\ReservationCreatedMail;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ReservationController extends BaseController 
{
    use AuthorizesRequests, ValidatesRequests, DispatchesJobs;

    public function __construct() 
    {
        $this->middleware(['auth']);
    }  

    /**
     * Display a listing of reservations for authenticated user.
     *
     * @return \Illuminate\View\View
     */
    public function index(): \Illuminate\View\View 
    {  
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $reservations = $user->reservations()->with('room')->orderBy('start_at')->get();  
        return view('reservations.index', compact('reservations'));  
    }  

    /**
     * Show the form for creating a new reservation.
     *
     * @return \Illuminate\View\View
     */
    public function create(): \Illuminate\View\View 
    {  
        $rooms = \App\Models\Room::all();  
        return view('reservations.create', compact('rooms'));  
    }  

    /**
     * Store a newly created reservation.
     *
     * @param CreateReservationRequest $req
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(CreateReservationRequest $req): \Illuminate\Http\RedirectResponse 
    {  
        /** @var array{room_id: int, start_at: string, end_at: string} */
        $data = $req->validated();
        
        try {
            $reservation = DB::transaction(function () use ($data) {
                // Lock all reservations for this room to prevent race condition
                $overlap = Reservation::where('room_id', $data['room_id'])  
                    ->lockForUpdate()
                    ->where('start_at', '<', $data['end_at'])  
                    ->where('end_at', '>', $data['start_at'])  
                    ->whereIn('status', ['pending','active'])  
                    ->exists();  
                    
                if ($overlap) {  
                    throw new \Exception('Vybrané období je již obsazeno.');
                }  

                /** @var \App\Models\User $user */
                $user = Auth::user();
                
                $reservation = Reservation::create([  
                    'user_id' => $user->getKey(),  
                    'room_id' => $data['room_id'],  
                    'start_at' => $data['start_at'],  
                    'end_at' => $data['end_at'],  
                    'status' => 'pending',  
                ]);  

                $reservation->token_valid_from = $reservation->start_at->subMinutes(
                    config('reservations.token_valid_before_minutes', 5)
                );  
                $reservation->token_expires_at = $reservation->end_at->addMinutes(
                    config('reservations.token_valid_after_minutes', 5)
                );  
                $reservation->save();  

                return $reservation;
            });

            Mail::to(Auth::user()->email)->send(new ReservationCreatedMail($reservation));

            // Dispatch jobs using the Dispatchable trait so PHPStan can infer types
            TurnOnShellyJob::dispatch($reservation)->delay(
                $reservation->start_at->subMinutes(
                    config('reservations.turn_on_before_minutes', 1)
                )
            );
            TurnOffShellyJob::dispatch($reservation)->delay(
                $reservation->end_at->addMinutes(
                    config('reservations.turn_off_after_minutes', 2)
                )
            );
            
            return redirect()->route('reservations.show', $reservation->id);
        } catch (\Exception $e) {
            return back()->withErrors(['slot' => $e->getMessage()])->withInput();
        }
    }  

    /**
     * Display the specified reservation.
     *
     * @param Reservation $reservation
     * @return \Illuminate\View\View
     */
    public function show(Reservation $reservation): \Illuminate\View\View
    {  
        $this->authorize('view', $reservation);
        return view('reservations.show', compact('reservation'));  
    }  

    /**
     * Get QR code image for reservation access.
     *
     * @param Reservation $reservation
     * @return \Illuminate\Http\Response
     */
    public function showQr(Reservation $reservation): \Illuminate\Http\Response
    {  
        $this->authorize('viewQr', $reservation);
        /** @var \SimpleSoftwareIO\QrCode\Generator $qrcode */
        $qrcode = QrCode::format('png')->size(400);
        $png = $qrcode->generate($reservation->access_token);
        /** @var \Illuminate\Http\Response $response */
        $response = response($png);
        return $response->header('Content-Type', 'image/png');  
    }  

    /**
     * Remove the specified reservation.
     *
     * @param Reservation $reservation
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Reservation $reservation): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('delete', $reservation);

        // Optionally, we could dispatch cancellation events or remove queued jobs.
        $reservation->delete();

        return redirect()->route('reservations.index')->with('status', 'Rezervace byla smazána.');
    }
}  