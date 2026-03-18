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