<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../src/Promocion.php';

class PromocionTest extends TestCase
{
    private PDO $conexion;
    private Promocion $promocion;

    protected function setUp(): void
    {
        $this->conexion = new PDO(
            'mysql:host=localhost;dbname=bebidas_test;charset=utf8mb4',
            'root',
            ''
        );

        $this->conexion->setAttribute(
            PDO::ATTR_ERRMODE,
            PDO::ERRMODE_EXCEPTION
        );

        $this->conexion->exec("DELETE FROM promociones");

        $this->promocion = new Promocion($this->conexion);
    }

    /*
     * TEST 1
     * El administrador crea un cupón válido
     */
    public function testAdministradorCreaCupon(): void
    {
        $id = $this->promocion->crear(
            'CERVEZA2X1',
            'Cupón de cerveza',
            50.00,
            '2026-11-21',
            '2026-12-21'
        );

        $cupon = $this->promocion->buscar($id);

        $this->assertEquals('CERVEZA2X1', $cupon['codigo']);
        $this->assertEquals(50.00, $cupon['descuento']);
        $this->assertEquals('activa', $cupon['estado']);
    }

    /*
     * TEST 2
     * El administrador no puede crear un cupón sin código
     */
    public function testNoPermitirCuponSinCodigo(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->promocion->crear(
            '',
            'Cupón sin código',
            20.00,
            '2026-11-21',
            '2026-12-21'
        );
    }

    /*
     * TEST 3
     * El administrador no puede crear un cupón con descuento negativo
     */
    public function testNoPermitirDescuentoNegativo(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->promocion->crear(
            'VINOHOGA',
            'Descuento negativo',
            -10.00,
            '2026-11-21',
            '2026-12-21'
        );
    }

    /*
     * TEST 4
     * El cliente aplica un cupón válido
     */
    public function testClienteAplicaCuponValido(): void
    {
        $this->promocion->crear(
            'CERVEZA2X1',
            'Cupón de cerveza',
            50.00,
            '2026-11-21',
            '2026-12-21'
        );

        $precioFinal = $this->promocion->aplicarCupon('CERVEZA2X1', 40.00);

        $this->assertEqualsWithDelta(20.00, $precioFinal, 0.01);
    }

    /*
     * TEST 5
     * El cliente no puede aplicar un cupón que no existe
     */
    public function testClienteNoAplicaCuponInexistente(): void
    {
        $this->expectException(DomainException::class);

        $this->promocion->aplicarCupon('CODIGO_FALSO', 40.00);
    }

    /*
     * TEST 6
     * El cliente no puede aplicar un cupón desactivado
     */
    public function testClienteNoAplicaCuponDesactivado(): void
    {
        $id = $this->promocion->crear(
            'AGUAMINERAL10',
            'Cupón de agua',
            10.00,
            '2026-11-21',
            '2026-12-21'
        );

        $this->promocion->desactivar($id);

        $this->expectException(DomainException::class);

        $this->promocion->aplicarCupon('AGUAMINERAL10', 40.00);
    }
}