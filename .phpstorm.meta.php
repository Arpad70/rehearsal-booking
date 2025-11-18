<?php
// Laravel helper function type hints for IDEs
// https://laravel.com/docs/helpers

namespace PHPSTORM_META {
    override(\auth(), type('@\Illuminate\Contracts\Auth\Guard'));
    override(\auth('api'), type('@\Illuminate\Contracts\Auth\Guard'));
    
    override(\config(), map([
        '' => '@',
    ]));
}
