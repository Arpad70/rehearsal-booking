#!/usr/bin/env python3  
from flask import Flask, request, jsonify  
import requests  
import os  
from dotenv import load_dotenv  
load_dotenv()  

app = Flask(__name__)  
SHELLY_USERNAME = os.getenv('SHELLY_USERNAME')  
SHELLY_PASSWORD = os.getenv('SHELLY_PASSWORD')  

@app.route('/api/gateway/control', methods=['POST'])  
def control():  
    data = request.json or {}  
    action = data.get('action')  
    room_id = data.get('room_id')  
    reservation_id = data.get('reservation_id')  
    shelly_ip = data.get('shelly_ip') or os.getenv(f'SHELLY_IP_{room_id}')  
    if not shelly_ip:  
        return jsonify({'status':'error','reason':'no_shelly_ip'}),400  

    try:  
        if action == 'turn_on_lights':  
            url = f'http://{shelly_ip}/rpc/Switch.Set'  
            resp = requests.post(url, json={'id':0,'on':True}, timeout=3)  
            return jsonify({'status':'ok','resp':resp.text}), 200  
        if action == 'turn_off_lights':  
            url = f'http://{shelly_ip}/rpc/Switch.Set'  
            resp = requests.post(url, json={'id':0,'on':False}, timeout=3)  
            return jsonify({'status':'ok','resp':resp.text}), 200  
    except Exception as e:  
        return jsonify({'status':'error','reason':str(e)}),500  

    return jsonify({'status':'error','reason':'unknown_action'}),400  

if __name__ == '__main__':  
    app.run(host='0.0.0.0', port=int(os.getenv('PORT', '5000')))  