
<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../src/Bebida.php';

class BebidaTest extends TestCase
{
    private PDO $conexion;
    private Bebida $bebida;
    private int $idBar;

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

        $this->conexion->exec("DELETE FROM pedidos");
        $this->conexion->exec("DELETE FROM bebidas");
        $this->conexion->exec("DELETE FROM bares");

        $this->conexion->exec(
            "INSERT INTO bares (nombre) VALUES ('Bar Central')"
        );

        $this->idBar = (int) $this->conexion->lastInsertId();

        $this->bebida = new Bebida($this->conexion);
    }

    /*
     * TEST 1
     * Registrar una bebida en el inventario
     */
    public function testRegistrarBebida(): void
    {
        $id = $this->bebida->registrar(
            'Coca-Cola',
            false,
            50
        );
        $this->assertGreaterThan(0, $id);

        $bebida = $this->bebida->buscar($id);
        $this->assertEquals('Coca-Cola', $bebida['nombre']);
        $this->assertEquals(50, $bebida['cantidad']);
        $this->assertEquals(0, $bebida['alcoholica']);
    }

    /*
     * TEST 2
     * Consultar las existencias de una bebida
     */
    public function testConsultarExistencias(): void
    {
        $id = $this->bebida->registrar(
            'Cerveza',
            true,
            40
        );

        $bebida = $this->bebida->buscar($id);

        $this->assertEquals(40, $bebida['cantidad']);
    }

      /*
     * TEST 3
     * Contar todas las unidades del almacén
     */
    public function testContarUnidadesAlmacen(): void
    {
        $this->bebida->registrar('Cerveza', true, 50);
        $this->bebida->registrar('Coca-Cola', false, 30);
        $this->bebida->registrar('Agua', false, 20);

        $total = $this->bebida->totalUnidades();

        $this->assertEquals(100, $total);
    }

    /*
     * TEST 4
     * Contar las unidades de bebidas alcohólicas
     */
    public function testContarUnidadesAlcoholicas(): void
    {
        $this->bebida->registrar('Cerveza', true, 50);
        $this->bebida->registrar('Vino', true, 15);
        $this->bebida->registrar('Agua', false, 30);

        $total = $this->bebida->contarAlcoholicas();

        $this->assertEquals(65, $total);
    }

    /*
     * TEST 5
     * Contar las unidades de bebidas sin alcohol
     */
    public function testContarUnidadesSinAlcohol(): void
    {
        $this->bebida->registrar('Coca-Cola', false, 30);
        $this->bebida->registrar('Agua', false, 20);
        $this->bebida->registrar('Cerveza', true, 50);

        $total = $this->bebida->contarSinAlcohol();
        $this->assertEquals(50, $total);
    }

    /*
     * TEST 6
     * Añadir unidades al inventario
     */
    public function testAnadirUnidadesInventario(): void
    {
        $id = $this->bebida->registrar(
            'Cerveza',
            true,
            50
        );

        $resultado = $this->bebida->anadirStock($id, 20);

        $this->assertTrue($resultado);

        $bebida = $this->bebida->buscar($id);

        $this->assertEquals(70, $bebida['cantidad']);
    }

    /*
     * TEST 7
     * Descontar unidades del inventario
     */
    public function testDescontarUnidadesInventario(): void
    {
        $id = $this->bebida->registrar(
            'Coca-Cola',
            false,
            50
        );

        $resultado = $this->bebida->descontarStock($id, 15);

        $this->assertTrue($resultado);

        $bebida = $this->bebida->buscar($id);

        $this->assertEquals(35, $bebida['cantidad']);
    }

    /*
     * TEST 8
     * Impedir una entrega cuando no hay existencias suficientes
     */
    public function testImpedirEntregaSinExistenciasSuficientes(): void
    {
        $id = $this->bebida->registrar(
            'Cerveza',
            true,
            10
        );

        $this->expectException(DomainException::class);

        $this->bebida->descontarStock($id, 20);
    }

    /*
     * TEST 9
     * No permitir registrar una bebida sin nombre
     */
    public function testNoPermitirRegistrarBebidaSinNombre(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->bebida->registrar(
            '',
            false,
            20
        );
    }

    /*
     * TEST 10
     * Registrar un pedido para un bar y comprobar el descuento del inventario
     */
    public function testRegistrarPedidoYDescontarInventario(): void
    {
        $id = $this->bebida->registrar(
            'Coca-Cola',
            false,
            50
        );

        $resultado = $this->bebida->registrarEntrega(
            $this->idBar,
            $id,
            10
        );

        $this->assertTrue($resultado);

        // Comprobar que se han descontado las unidades del inventario
        $bebida = $this->bebida->buscar($id);

        $this->assertEquals(40, $bebida['cantidad']);

        // Comprobar que el pedido se ha guardado en la base de datos
        $consulta = $this->conexion->prepare(
            "SELECT * FROM pedidos
             WHERE bar_id = ? AND bebida_id = ?"
        );

        $consulta->execute([
            $this->idBar,
            $id
        ]);

        $pedido = $consulta->fetch(PDO::FETCH_ASSOC);

        $this->assertNotFalse($pedido);
        $this->assertEquals(10, $pedido['cantidad']);
        $this->assertEquals($this->idBar, $pedido['bar_id']);
        $this->assertEquals($id, $pedido['bebida_id']);
    }
}


