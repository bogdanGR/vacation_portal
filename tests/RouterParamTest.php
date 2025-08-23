<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Core\Router;

final class RouterParamTest extends TestCase
{
    public function testRouteWithIdParamDispatches(): void
    {
        $router = new Router();

        // Dummy controller we can assert against
        $called = new class {
            public static array $lastParams = [];
            public function edit(array $params): void {
                self::$lastParams = $params;
            }
        };

        $router->get('/users/{id}/edit', [get_class($called), 'edit']);

        // Buffer output to avoid “Not Found” echo interfering
        ob_start();
        $router->dispatch('GET', '/users/42/edit');
        ob_end_clean();

        $this->assertSame(['id' => '42'], $called::$lastParams);
    }
}
