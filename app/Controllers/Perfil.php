<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Perfil extends BaseController
{
    // 1. Carga la vista del formulario
    public function cambiarPassword()
    {
        $data = [
            'titulo'   => 'Cambiar Contraseña | VitalGym',
            'username' => obtener_username(),
        ];

        return view('html/main', $data)
             . view('html/PerfilPassword', $data)
             . view('html/footer');
    }

    // 2. Procesa el cambio de contraseña vía AJAX
    public function actualizarPasswordAjax()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'mensaje' => 'Petición no válida.']);
        }

        $passActual  = $this->request->getPost('pass_actual');
        $passNueva   = $this->request->getPost('pass_nueva');
        $passConfirm = $this->request->getPost('pass_confirm');

        // Validaciones básicas
        if (empty($passActual) || empty($passNueva) || empty($passConfirm)) {
            return $this->response->setJSON(['status' => 'error', 'mensaje' => 'Todos los campos son obligatorios.', 'token' => csrf_hash()]);
        }

        if ($passNueva !== $passConfirm) {
            return $this->response->setJSON(['status' => 'error', 'mensaje' => 'Las contraseñas nuevas no coinciden.', 'token' => csrf_hash()]);
        }

        if (strlen($passNueva) < 8) {
            return $this->response->setJSON(['status' => 'error', 'mensaje' => 'La nueva contraseña debe tener al menos 8 caracteres.', 'token' => csrf_hash()]);
        }

        // Obtener el proveedor de usuarios de Shield y al usuario activo
        $users = auth()->getProvider();
        $usuarioActivo = auth()->user();
        
        // Extraemos los datos completos del usuario de la BD
        $userEntity = $users->findById($usuarioActivo->id);

        // Validar que la contraseña actual ingresada coincida con la encriptada en la BD
        if (!password_verify($passActual, $userEntity->password_hash)) {
            return $this->response->setJSON(['status' => 'error', 'mensaje' => 'La contraseña actual ingresada es incorrecta.', 'token' => csrf_hash()]);
        }

        // Si todo es correcto, le asignamos la nueva contraseña (Shield la encripta automáticamente)
        $userEntity->fill(['password' => $passNueva]);
        $users->save($userEntity);

        return $this->response->setJSON([
            'status'  => 'success', 
            'mensaje' => '¡Tu contraseña ha sido actualizada con éxito por seguridad!', 
            'token'   => csrf_hash()
        ]);
    }
}