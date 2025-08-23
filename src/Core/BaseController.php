<?php
namespace App\Core;

/**
 * Base Controller class.
 *
 * Provides common helpers for rendering views, handling redirects,
 * and enforcing authentication/authorization.
 */
class BaseController
{
    /**
     * Render a view file within the main layout.
     *
     * @param string $view Relative path under src/Views (e.g. "employee/index")
     * @param array  $data Variables to extract and make available to the view
     */
    protected function render(string $view, array $data = []): void
    {
        extract($data);
        $viewFile = __DIR__ . "/../Views/$view.php";
        $layoutFile = __DIR__ . "/../Views/layout.php";
        ob_start();
        require $viewFile;
        $content = ob_get_clean();
        require $layoutFile;
    }

    /**
     * Redirect to another path and terminate execution.
     *
     * @param string $to Path or URL to redirect the user to
     */
    protected function redirect(string $to): void
    {
        header("Location: $to");
        exit;
    }

    /**
     * Get the currently authenticated user.
     *
     * @return array|null User data array from the session, or null if not logged in
     */
    protected function user(): ?array { return $_SESSION['user'] ?? null; }

    /**
     * Require that a user is logged in.
     * If not logged in, redirect to /login.
     */
    protected function requireLogin(): void
    {
        if (!$this->user()) $this->redirect('/login');
    }

    /**
     * Require that the current user is a manager.
     * If not logged in or not a manager, redirect to /employee.
     */
    protected function requireManager(): void
    {
        $this->requireLogin();
        if (($this->user()['role'] ?? '') !== 'manager') $this->redirect('/employee');
    }

    /**
     * Get current CSRF token string.
     * @return string
     */
    protected function getCsrfToken(): string
    {
        return $_SESSION['csrf_token'] ?? '';
    }

    /**
     * Verify CSRF token for POST actions. Sends 403 & exits on failure.
     * @return void
     */
    protected function verifyCsrf(): void
    {
        $token = $_POST['csrf_token'] ?? '';
        $sessionToken = $_SESSION['csrf_token'] ?? '';
        if (!$token || !$sessionToken || !hash_equals($sessionToken, $token)) {
            http_response_code(403);
            echo 'CSRF validation failed';
            exit;
        }
    }

}
