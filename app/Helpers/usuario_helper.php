<?php

if (!function_exists('obtener_username')) {
    /**
     * Obtiene el username del usuario logueado actualmente con Shield.
     * Retorna false si no hay sesión.
     */
    function obtener_username()
    {
        if (!auth()->loggedIn()) {
            return false;
        }
        return auth()->user()->username;
    }
}


if (!function_exists('obtener_sucursal_usuario')) {
    function obtener_sucursal_usuario()
    {
        if (!auth()->loggedIn()) {
            return 'SUCUR0000X';
        }

        $userId = auth()->id();

        // Llamamos al modelo directamente con la función global de CI4
        // (Ajusta el nombre de la clase si tu modelo se llama distinto)
        $gymModel = model(\App\Models\GymnasiosModel::class); 
        
        // Hacemos la consulta
        $gym = $gymModel->where('users_id', $userId)->first();

        if ($gym) {
            // Nota: Al usar el modelo, normalmente te devuelve un array, por eso usamos corchetes
            return 'SUCUR' . str_pad($gym['idGymnasios'], 5, '0', STR_PAD_LEFT);
        }

       // return 'SUCUR00001'; 
    }
}