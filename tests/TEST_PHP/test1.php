<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/Cliente.php';

class ClienteTest extends TestCase
{
    private PDO $conexion;
    private Cliente $cliente;

    protected function setUp(): void
    {
        $this->conexion = new PDO(
            'mysql:host=localhost;dbname=tienda_test;charset=utf8mb4',
            'root',
            ''
        );

        $this->conexion->setAttribute(
            PDO::ATTR_ERRMODE,
            PDO::ERRMODE_EXCEPTION
        );

        $this->conexion->exec("DELETE FROM clientes");

        $this->cliente = new Cliente($this->conexion);
    }

    /*
     * TEST 1
     * Registrar cliente con dos apellidos
     */
    public function testRegistrarClienteConDosApellidos(): void
    {
        $id = $this->cliente->registrar(
            'Andrés',
            'Gómez',
            'Ocaña',
            '12345678Z',
            'andres@tienda.com'
        );

        $this->assertGreaterThan(0, $id);

        $cliente = $this->cliente->buscar($id);

        $this->assertEquals('Andrés', $cliente['nombre']);
        $this->assertEquals('Gómez', $cliente['apellido1']);
        $this->assertEquals('Ocaña', $cliente['apellido2']);
        $this->assertEquals('12345678Z', $cliente['dni_nie']);
        $this->assertEquals('andres@tienda.com', $cliente['email']);
    }

    /*
     * TEST 2
     * Registrar cliente con solamente un apellido
     */
    public function testRegistrarClienteConUnSoloApellido(): void
    {
        $id = $this->cliente->registrar(
            'Juan',
            'García',
            null,
            '87654321X',
            'juan@tienda.com'
        );

        $cliente = $this->cliente->buscar($id);

        $this->assertEquals('Juan', $cliente['nombre']);
        $this->assertEquals('García', $cliente['apellido1']);
        $this->assertNull($cliente['apellido2']);
    }

    /*
     * TEST 3
     * Modificar los datos del cliente
     */
    public function testModificarCliente(): void
    {
        $id = $this->cliente->registrar(
            'Andrés',
            'Gómez',
            'Ocaña',
            '12345678Z',
            'andres@tienda.com'
        );

        $resultado = $this->cliente->modificar(
            $id,
            'Andrés',
            'Gómez',
            'Pérez',
            '12345678Z',
            'andres.nuevo@tienda.com'
        );

        $this->assertTrue($resultado);

        $cliente = $this->cliente->buscar($id);

        $this->assertEquals('Andrés', $cliente['nombre']);
        $this->assertEquals('Gómez', $cliente['apellido1']);
        $this->assertEquals('Pérez', $cliente['apellido2']);
        $this->assertEquals('12345678Z', $cliente['dni_nie']);
        $this->assertEquals(
            'andres.nuevo@tienda.com',
            $cliente['email']
        );
    }

    /*
     * TEST 4
     * No permitir cliente sin primer apellido
     */
    public function testNoPermitirClienteSinPrimerApellido(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->cliente->registrar(
            'Andrés',
            '',
            'Ocaña',
            '12345678Z',
            'andres@tienda.com'
        );
    }

    /*
     * TEST 5
     * No permitir cliente duplicado por DNI/NIE
     */
    public function testNoPermitirDniNieDuplicado(): void
    {
        $this->cliente->registrar(
            'Andrés',
            'Gómez',
            'Ocaña',
            '12345678Z',
            'andres@tienda.com'
        );

        $this->expectException(DomainException::class);

        $this->cliente->registrar(
            'Juan',
            'García',
            'López',
            '12345678Z',
            'juan@tienda.com'
        );
    }

    /*
     * TEST 6
     * No permitir email duplicado
     */
    public function testNoPermitirEmailDuplicado(): void
    {
        $this->cliente->registrar(
            'Andrés',
            'Gómez',
            'Ocaña',
            '12345678Z',
            'andres@tienda.com'
        );

        $this->expectException(DomainException::class);

        $this->cliente->registrar(
            'Juan',
            'García',
            'López',
            '87654321X',
            'andres@tienda.com'
        );
    }
}

?>