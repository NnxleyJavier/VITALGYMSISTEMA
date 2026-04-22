<?php

if (!function_exists('obtener_id_gimnasio')) {
    function obtener_id_gimnasio()
    {
        // 1. Si no hay sesión, abortamos
        if (!auth()->loggedIn()) {
            return null;
        }

        // 2. Si el usuario es el Superadmin (el dueño absoluto), ve todo
        if (auth()->user()->inGroup('superadmin')) {
            return 'TODOS';
        }

        // 3. Consultamos directamente la tabla users para ver a qué gimnasio pertenece
        $db = \Config\Database::connect();
        
        $usuario = $db->table('users')
                      ->select('id_gimnasio')
                      ->where('id', auth()->user()->id)
                      ->get()
                      ->getRow();

        // Retornamos el ID de su gimnasio, o null si no tiene asignado
        return $usuario ? $usuario->id_gimnasio : null;
    }
}