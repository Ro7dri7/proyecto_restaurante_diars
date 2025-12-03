// Ubicación: public/js/pedido.js

$(document).ready(function () {
    
    // --- 1. VARIABLES GLOBALES ---
    // PRODUCTOS_DB se define en tu pedido_form.php
    let productosFiltrados = [...PRODUCTOS_DB]; // Copia inicial
    let carrito = [];

    // --- 2. FUNCIÓN PRINCIPAL DE FILTRADO ---
    function actualizarFiltros() {
        const filtroCategoria = $('#filtroCategoria').val();
        const terminoBusqueda = $('#filtroBusqueda').val().toLowerCase().trim();

        // Filtrar los productos en base a los criterios
        productosFiltrados = PRODUCTOS_DB.filter(producto => {
            const coincideCategoria = !filtroCategoria || producto.categoria == filtroCategoria;
            const nombreCoincide = producto.nombre.toLowerCase().includes(terminoBusqueda);
            const idCoincide = String(producto.id).includes(terminoBusqueda);
            const coincideBusqueda = nombreCoincide || idCoincide;
            return coincideCategoria && coincideBusqueda;
        });

        // Actualizar el contador de resultados
        $('#countProductos').text(productosFiltrados.length);

        // Actualizar el <select> de productos
        const selectProducto = $('#producto');
        selectProducto.empty().append('<option value="">-- Seleccione un Producto --</option>');

        if (productosFiltrados.length === 0) {
            selectProducto.prop('disabled', true);
            $('#btnAnadirProducto').prop('disabled', true);
        } else {
            // Llenar el select con los productos filtrados
            productosFiltrados.forEach(prod => {
                selectProducto.append(`
                    <option value="${prod.id}" 
                            data-nombre="${prod.nombre}" 
                            data-precio="${prod.precio}">
                        [ID: ${prod.id}] ${prod.nombre} - S/ ${parseFloat(prod.precio).toFixed(2)}
                    </option>
                `);
            });
            selectProducto.prop('disabled', false);
            $('#btnAnadirProducto').prop('disabled', false);
        }

        // Resetear la selección actual
        selectProducto.val('');
    }

    // --- 3. EVENTOS PARA LOS FILTROS ---
    $('#filtroCategoria').on('change', actualizarFiltros);
    $('#filtroBusqueda').on('input', actualizarFiltros);

    // --- 4. LÓGICA DEL CARRITO (IGUAL QUE TU CÓDIGO ORIGINAL) ---
    $('#btnAnadirProducto').on('click', function() {
        const productoOpcion = $('#producto option:selected');
        const id = productoOpcion.val();
        const nombre = productoOpcion.data('nombre');
        const precio = parseFloat(productoOpcion.data('precio'));
        const cantidad = parseInt($('#cantidad').val());

        if (!id || cantidad <= 0 || isNaN(precio)) {
            alert('Por favor, seleccione un producto y una cantidad válida.');
            return;
        }

        const subtotalItem = (precio * cantidad).toFixed(2);
        const fila = `
            <tr data-idproducto="${id}">
                <td>${id}</td>
                <td>${nombre}</td>
                <td>${cantidad}</td>
                <td>S/ ${precio.toFixed(2)}</td>
                <td>S/ ${subtotalItem}</td>
                <td><button type="button" class="btn btn-quitar">Quitar</button></td>
            </tr>
        `;
        $('#cuerpoTablaDetalle').append(fila);

        const inputsOcultos = `
            <div id="item-oculto-${id}">
                <input type="hidden" name="productos[id][]" value="${id}">
                <input type="hidden" name="productos[cantidad][]" value="${cantidad}">
                <input type="hidden" name="productos[precio][]" value="${precio}">
                <input type="hidden" name="productos[subtotal][]" value="${subtotalItem}">
            </div>
        `;
        $('#itemsOcultosParaEnvio').append(inputsOcultos);

        actualizarTotales();
        $('#producto').val('');
        $('#cantidad').val(1);
    });

    $('#cuerpoTablaDetalle').on('click', '.btn-quitar', function() {
        const fila = $(this).closest('tr');
        const idProducto = fila.data('idproducto');
        fila.remove();
        $(`#item-oculto-${idProducto}`).remove();
        actualizarTotales();
    });

    // --- 5. FUNCIÓN PARA CALCULAR TOTALES ---
    function actualizarTotales() {
        let subtotalGeneral = 0;
        $('#cuerpoTablaDetalle tr').each(function() {
            let subtotalTexto = $(this).find('td:nth-child(5)').text();
            let subtotalItem = parseFloat(subtotalTexto.replace('S/ ', ''));
            subtotalGeneral += subtotalItem;
        });

        const igv = subtotalGeneral * 0.18;
        const total = subtotalGeneral + igv;

        $('#spanSubtotal').text(subtotalGeneral.toFixed(2));
        $('#spanIGV').text(igv.toFixed(2));
        $('#spanTotal').text(total.toFixed(2));

        $('#hiddenSubtotal').val(subtotalGeneral.toFixed(2));
        $('#hiddenIGV').val(igv.toFixed(2));
        $('#hiddenTotal').val(total.toFixed(2));
    }

    // --- 6. INICIALIZAR ---
    actualizarFiltros();
});