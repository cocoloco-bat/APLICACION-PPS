#Test de ejmeplo de la parte de Clientes , como personas fisicas
import os
import sys
import unittest

sys.path.insert(0, os.path.join(os.path.dirname(__file__), "..", "src"))

from pedido import calcular_total, gasto_total_cliente, preparar_nota


class GastosPedidoTest(unittest.TestCase):

    # TEST 1: 5 cajas de Fanta a 12 € = 60 € + 21 % IVA = 72,60 €
    def test_gasto_5_cajas_de_fanta(self):
        resultado = calcular_total([{"precio": 12.00, "cajas": 5}])

        self.assertEqual(resultado["subtotal"], 60.00)
        self.assertEqual(resultado["iva"], 12.60)
        self.assertEqual(resultado["total"], 72.60)

    # TEST 2: Fanta 24 € + Coca-Cola 42 € + Agua 24 € = 90 € -> 108,90 €
    def test_gasto_pedido_con_varios_productos(self):
        resultado = calcular_total([
            {"precio": 12.00, "cajas": 2},
            {"precio": 14.00, "cajas": 3},
            {"precio": 6.00, "cajas": 4},
        ])

        self.assertEqual(resultado["subtotal"], 90.00)
        self.assertEqual(resultado["total"], 108.90)

    # TEST 3: Con 49 cajas no hay descuento
    def test_sin_descuento_con_49_cajas(self):
        resultado = calcular_total([{"precio": 6.00, "cajas": 49}])

        self.assertEqual(resultado["descuento"], 0)

    # TEST 4: Con 50 cajas, 10 % de descuento: 300 € -> 270 € -> 326,70 €
    def test_descuento_con_50_cajas(self):
        resultado = calcular_total([{"precio": 6.00, "cajas": 50}])

        self.assertEqual(resultado["descuento"], 30.00)
        self.assertEqual(resultado["total"], 326.70)

    # TEST 5: Un pedido sin productos cuesta 0 €
    def test_pedido_vacio_cuesta_0(self):
        resultado = calcular_total([])

        self.assertEqual(resultado["total"], 0)

    # TEST 6: No permitir cajas negativas (restarian dinero al pedido)
    def test_no_permitir_cajas_negativas(self):
        with self.assertRaises(ValueError):
            calcular_total([{"precio": 12.00, "cajas": -5}])

    # TEST 7: Lo que ha gastado un cliente en todos sus pedidos
    def test_gasto_total_de_un_cliente(self):
        self.assertEqual(gasto_total_cliente([72.60, 108.90, 326.70]), 508.20)

    # TEST 8: Un cliente sin pedidos ha gastado 0 €
    def test_cliente_sin_pedidos_ha_gastado_0(self):
        self.assertEqual(gasto_total_cliente([]), 0)


class NotasPedidoTest(unittest.TestCase):

    # TEST 9: Una nota normal se guarda igual
    def test_nota_normal_se_guarda_igual(self):
        self.assertEqual(
            preparar_nota("Entregar antes de las 10"),
            "Entregar antes de las 10"
        )

    # TEST 10: La nota es opcional
    def test_nota_vacia_esta_permitida(self):
        self.assertEqual(preparar_nota(""), "")
        self.assertEqual(preparar_nota(None), "")

    # TEST 11: Se quitan los espacios del principio y del final
    def test_nota_quita_espacios_sobrantes(self):
        self.assertEqual(preparar_nota("   Puerta trasera   "), "Puerta trasera")
        self.assertEqual(preparar_nota("      "), "")

    # TEST 12: Una nota de 200 letras se acepta (límite)
    def test_nota_de_200_letras_se_acepta(self):
        nota = "a" * 200

        self.assertEqual(preparar_nota(nota), nota)

    # TEST 13: Una nota de 201 letras se rechaza
    def test_nota_de_201_letras_se_rechaza(self):
        with self.assertRaises(ValueError):
            preparar_nota("a" * 201)

    # TEST 14: El codigo HTML se neutraliza (Este lo vamos a mirar en grupo , y preguntar sl profe por que es algo como securizar el codigp)
    def test_nota_con_codigo_malicioso_se_neutraliza(self):
        limpia = preparar_nota("<script>alert('hackeado')</script>")

        self.assertNotIn("<script>", limpia)
        self.assertIn("&lt;script&gt;", limpia)

    # TEST 15: Las tildes y la ñ se guardan bien
    def test_nota_con_tildes_y_enie(self):
        nota = "Llamar a Begoña al llegar, está en el almacén"

        self.assertEqual(preparar_nota(nota), nota)


if __name__ == "__main__":
    unittest.main()