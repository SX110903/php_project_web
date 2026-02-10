<?php

namespace App\Controllers;

use App\Validators\Validator;

/**
 * Auth Controller
 * Maneja autenticación: login, registro, recuperación de contraseña
 */
class AuthController extends BaseController
{
    /**
     * Muestra el formulario de login
     */
    public function showLogin(): void
    {
        if ($this->authService->isAuthenticated()) {
            $this->redirect('/dashboard.php');
        }

        $this->render('auth/login', [
            'flash' => $this->getFlashMessage()
        ]);
    }

    /**
     * Procesa el login
     */
    public function login(): void
    {
        if (!$this->verifyCsrfToken()) {
            $this->setFlashMessage('error', 'Token de seguridad inválido');
            $this->redirect('/login.php');
        }

        $data = $this->getPostData();

        // Validar datos
        $validator = new Validator($data);
        $validator
            ->required('email', 'El email es requerido')
            ->email('email', 'El email no es válido')
            ->required('password', 'La contraseña es requerida');

        if ($validator->fails()) {
            $this->setFlashMessage('error', $validator->getFirstError());
            $this->redirect('/login.php');
        }

        // Intentar login
        $result = $this->authService->login($data['email'], $data['password']);

        if (!$result['success']) {
            $this->setFlashMessage('error', $result['message']);
            $this->redirect('/login.php');
        }

        $this->redirect('/dashboard.php');
    }

    /**
     * Cierra sesión
     */
    public function logout(): void
    {
        $this->authService->logout();
        $this->setFlashMessage('success', 'Sesión cerrada exitosamente');
        $this->redirect('/login.php');
    }

    /**
     * Muestra el formulario de registro
     */
    public function showRegister(): void
    {
        if ($this->authService->isAuthenticated()) {
            $this->redirect('/dashboard.php');
        }

        $this->render('auth/register', [
            'flash' => $this->getFlashMessage()
        ]);
    }

    /**
     * Procesa el registro
     */
    public function register(): void
    {
        if (!$this->verifyCsrfToken()) {
            $this->setFlashMessage('error', 'Token de seguridad inválido');
            $this->redirect('/register.php');
        }

        $data = $this->getPostData();

        // Validar datos
        $validator = new Validator($data);
        $validator
            ->required('name', 'El nombre es requerido')
            ->minLength('name', 3, 'El nombre debe tener al menos 3 caracteres')
            ->required('email', 'El email es requerido')
            ->email('email', 'El email no es válido')
            ->required('password', 'La contraseña es requerida')
            ->minLength('password', 8, 'La contraseña debe tener al menos 8 caracteres')
            ->required('confirm_password', 'Confirma tu contraseña')
            ->matches('confirm_password', 'password', 'Las contraseñas no coinciden');

        if ($validator->fails()) {
            $this->setFlashMessage('error', $validator->getFirstError());
            $this->redirect('/register.php');
        }

        // Registrar usuario
        $result = $this->authService->register([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password']
        ]);

        if (!$result['success']) {
            $this->setFlashMessage('error', $result['message']);
            $this->redirect('/register.php');
        }

        $this->setFlashMessage('success', 'Registro exitoso. Ahora puedes iniciar sesión');
        $this->redirect('/login.php');
    }

    /**
     * Muestra el formulario de recuperación de contraseña
     */
    public function showForgotPassword(): void
    {
        if ($this->authService->isAuthenticated()) {
            $this->redirect('/dashboard.php');
        }

        $this->render('auth/forgot-password', [
            'flash' => $this->getFlashMessage()
        ]);
    }

    /**
     * Procesa la solicitud de recuperación
     */
    public function forgotPassword(): void
    {
        if (!$this->verifyCsrfToken()) {
            $this->setFlashMessage('error', 'Token de seguridad inválido');
            $this->redirect('/forgot-password.php');
        }

        $data = $this->getPostData();

        // Validar email
        $validator = new Validator($data);
        $validator
            ->required('email', 'El email es requerido')
            ->email('email', 'El email no es válido');

        if ($validator->fails()) {
            $this->setFlashMessage('error', $validator->getFirstError());
            $this->redirect('/forgot-password.php');
        }

        // Iniciar recuperación
        $result = $this->authService->initiatePasswordReset($data['email']);

        $this->setFlashMessage('success', $result['message']);
        $this->redirect('/forgot-password.php');
    }

    /**
     * Muestra el formulario de reseteo de contraseña
     */
    public function showResetPassword(): void
    {
        if ($this->authService->isAuthenticated()) {
            $this->redirect('/dashboard.php');
        }

        $query = $this->getQueryData();

        if (!isset($query['token'])) {
            $this->setFlashMessage('error', 'Token no proporcionado');
            $this->redirect('/forgot-password.php');
        }

        $this->render('auth/reset-password', [
            'flash' => $this->getFlashMessage(),
            'token' => $query['token']
        ]);
    }

    /**
     * Procesa el reseteo de contraseña
     */
    public function resetPassword(): void
    {
        if (!$this->verifyCsrfToken()) {
            $this->setFlashMessage('error', 'Token de seguridad inválido');
            $this->redirect('/forgot-password.php');
        }

        $data = $this->getPostData();

        // Validar datos
        $validator = new Validator($data);
        $validator
            ->required('token', 'Token no proporcionado')
            ->required('password', 'La contraseña es requerida')
            ->minLength('password', 8, 'La contraseña debe tener al menos 8 caracteres')
            ->required('confirm_password', 'Confirma tu contraseña')
            ->matches('confirm_password', 'password', 'Las contraseñas no coinciden');

        if ($validator->fails()) {
            $this->setFlashMessage('error', $validator->getFirstError());
            $this->redirect('/reset-password.php?token=' . $data['token']);
        }

        // Resetear contraseña
        $result = $this->authService->resetPassword($data['token'], $data['password']);

        if (!$result['success']) {
            $this->setFlashMessage('error', $result['message']);
            $this->redirect('/forgot-password.php');
        }

        $this->setFlashMessage('success', $result['message']);
        $this->redirect('/login.php');
    }
}
