<?php
$title = "Preventa - Captura";
include __DIR__ . '/../app/appPedidos.php';
include __DIR__ . '/layout/header.php';

// Puedes tomar datos de sesión para prefijar ruta/UV/etc.
$idRutaCompleta = $_SESSION['nombre'] ?? 0;
$idRuta = strtoupper(trim(explode('@', $idRutaCompleta)[0] ?? ''));
$unidad = $unidadOperacionalController->getIdUsuario($_SESSION['login_id'] ?? 0);
$pesoMaximo = $unidad[0]['CAPMAX'] ?? 0;
$pesoMinimo = $unidad[0]['CAPMIN'] ?? 0;
$carro = $unidad[0]['CARRO'] ?? '';

?>
<link rel="stylesheet" href="../tools/newStyle.css">

<main class="page" style="padding:18px;">
    <header class="page-head" style="max-width:1100px;margin:0 auto 14px;">
        <h1 class="page-title" style="margin:6px 0 4px; font-weight:800;">PEDIDO DE CARGA</h1>
        <p class="page-sub" style="margin:0;color:#8aa2b4;"><strong>Ruta <?= $idRuta ?> </strong>· agrega artículos y cantidades</p>
    </header>

    <!-- Capturador de renglones -->
    <section class="card" style="max-width:600px;margin:0 auto;padding:16px;font-size:1.05rem;">
        <h2 style="margin:0 0 14px;font-size:1.3rem;font-weight:800;">Agregar artículo</h2>

        <div class="grid" style="display:grid;grid-template-columns:1fr 0.6fr auto;gap:12px;align-items:end;">
            <div class="seg-lineas" id="segLineas" style="margin:6px 0 10px;">
                <button type="button" class="seg-btn is-active" data-linea="all">Todos</button>
                <button type="button" class="seg-btn" data-linea="Embutido">Embutidos</button>
                <button type="button" class="seg-btn" data-linea="Carnes frias">Carnes frías</button>
                <button type="button" class="seg-btn" data-linea="Queso">Queso</button>
                <button type="button" class="seg-btn" data-linea="Manteca">Manteca</button>
            </div>
            <div>
                <label class="lbl" for="articulo" style="font-weight:700;">Artículo</label>
                <select class="in" id="articulo" style="width:100%;padding:12px;font-size:1.05rem;border-radius:8px;">
                    <option value="">Selecciona un artículo</option>
                    <?php include __DIR__ . '/../partials/combo_articulos.php'; ?>
                </select>

            </div>
        </div>
        <div class="section-sep"></div>

        <div class="grid" style="display:grid;grid-template-columns:1fr 0.6fr auto;gap:12px;align-items:end;">
            <div>
                <label class="lbl" for="cantidad" style="font-weight:700;">Cantidad</label>
                <input
                    class="in" id="cantidad"
                    type="text" inputmode="decimal"
                    pattern="[0-9]*[.,]?[0-9]*"
                    placeholder="000"
                    style="padding:12px;font-size:1.05rem;border-radius:8px;width:88px;"
                    autocomplete="off" enterkeyhint="done">
            </div>

            <div>
                <button id="btnAgregar" class="app-btnCap" type="button" style="padding:12px 20px;font-size:1rem;">Agregar</button>
            </div>
        </div>
        <div class="section-sep"></div>
        <!-- Tabla -->
        <div style="overflow:auto;margin-top:16px;">
            <table class="tickets-table" style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="font-size:.85rem;">
                        <th style="padding:4px;">Artículo</th>
                        <th style="padding:4px;">Cantidad</th>
                        <th style="padding:4px;">Peso Unitario</th>
                        <th style="padding:4px;">Peso Total</th>
                        <th style="padding:4px;">Accion</th>
                    </tr>
                </thead>
                <tbody id="rows">
                    <tr>
                        <td colspan="3" style="padding:14px;">Sin renglones</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2"><strong>Renglones: <span id="count">0</span></strong></td>
                        <td colspan="3" style="text-align:right;"><strong>Total: <span id="totalKg">0.000</span> kg</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div>
            <label class="lbl" for="modelo" style="display:block;font-weight:700;margin-bottom:6px;">Modelo - <?php echo $carro; ?> </label>
            <label class="lbl" for="peso" style="display:block;font-weight:700;margin-bottom:6px;">Carga máxima / mínima: <?php echo $pesoMaximo; ?> / <?php echo $pesoMinimo; ?> kg</label>
        </div>

        <footer style="display:flex;gap:10px;justify-content:space-between;align-items:center;margin-top:14px;flex-wrap:wrap;">
            <div class="muted" style="font-size:1rem;"><span id="count">0</span> renglón(es)</div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <button id="btnLimpiar" class="btn-cancelar" type="button" style="padding:12px 18px;font-size:1rem;">Limpiar</button>
                <button id="btnGuardar" class="app-btnCap" type="button" style="padding:12px 18px;font-size:1rem;">Guardar pedido</button>
            </div>
        </footer>
        <section>
            <!-- Observaciones -->

            <div>
                <label class="lbl" for="obs" style="display:block;font-weight:700;margin-bottom:6px;">Observaciones</label>
                <textarea id="obs" class="in" placeholder="Ej. pedido tempranero o notas adicionales…"
                    style="width:100%;min-height:100px;resize:vertical;padding:12px;font-size:1.05rem;border-radius:8px;"></textarea>
            </div>
        </section>
        <div id="msg" class="muted" style="margin-top:10px;font-size:1rem;"></div>
    </section>



    <script>
        (() => {
            const qs = s => document.querySelector(s);
            const rows = qs('#rows');
            const msg = qs('#msg');
            const selectArt = qs('#articulo');

            const state = {
                items: [] // { id, nombre, cantidad, pesoUnit }
            };

            const e = v => (v ?? '').toString()
                .replaceAll('&', '&amp;').replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;').replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');

            function render() {
                if (state.items.length === 0) {
                    rows.innerHTML = `<tr><td colspan="4" style="padding:12px;">Sin renglones</td></tr>`;
                    qs('#count').textContent = 0;
                    qs('#totalKg').textContent = '0.000';
                    return;
                }

                let totalKg = 0;

                rows.innerHTML = state.items.map((it, i) => {
                    const pesoTotal = (Number(it.cantidad) * Number(it.pesoUnit)) || 0;
                    totalKg += pesoTotal;
                    return `
        <tr style="font-size:.75rem;">
          <td style="padding:4px;">${e(it.nombre)}</td>
          <td style="padding:4px;">
            <input type="text" inputmode="decimal" pattern="[0-9]*[.,]?[0-9]*"
                   value="${e(it.cantidad)}" data-i="${i}"
                   class="in qty-input qty" autocomplete="off" enterkeyhint="done" style="width: 45px;">
          </td>
          <td style="padding:4px;">${e(it.pesoUnit)} kg/u</td>
          <td style="padding:4px;">${pesoTotal.toFixed(3)} kg</td>
          <td style="padding:4px;">
            <button class="btn-cancelar btnDel btn-sm" data-i="${i}" type="button">X</button>
          </td>
        </tr>`;
                }).join('');

                qs('#count').textContent = state.items.length;
                qs('#totalKg').textContent = totalKg.toFixed(3);
            }

            // AGREGAR RENGLÓN
            qs('#btnAgregar').addEventListener('click', () => {
                const artSel = selectArt;
                const id = parseInt(artSel.value || '0', 10);
                const nombre = artSel.options[artSel.selectedIndex]?.text?.trim() || '';
                const pesoUnit = parseFloat(artSel.options[artSel.selectedIndex]?.dataset?.peso || '0'); // <<--
                const raw = qs('#cantidad').value.trim().replace(',', '.');
                const cantidad = parseFloat(raw);

                if (!id || !nombre) {
                    msg.textContent = 'Selecciona un artículo.';
                    return;
                }
                if (!isFinite(cantidad) || cantidad <= 0) {
                    msg.textContent = 'Indica una cantidad mayor a 0.';
                    return;
                }
                if (!isFinite(pesoUnit) || pesoUnit <= 0) {
                    msg.textContent = 'El artículo no tiene peso válido.';
                    return;
                }

                const dup = state.items.find(i => i.id === id);
                if (dup) {
                    dup.cantidad = +(dup.cantidad) + cantidad;
                    // dup.pesoUnit = pesoUnit; // normalmente no cambia, pero si quieres lo sincronizas
                } else {
                    state.items.push({
                        id,
                        nombre,
                        cantidad,
                        pesoUnit
                    }); // <<--
                }

                qs('#cantidad').value = '';
                msg.textContent = '';
                render();
                selectArt.focus();
            });

            // Editar cantidad en la tabla
            rows.addEventListener('input', ev => {
                if (ev.target.classList.contains('qty')) {
                    const i = +ev.target.dataset.i;
                    let v = parseFloat(ev.target.value.replace(',', '.'));
                    if (!isFinite(v) || v < 0) v = 0;
                    state.items[i].cantidad = v;
                    render(); // re-render para actualizar peso total por fila y total general
                }
            });

            // Eliminar renglón
            rows.addEventListener('click', ev => {
                const btn = ev.target.closest('.btnDel, .btn-cancelar');
                if (!btn) return;
                const i = +btn.dataset.i;
                if (Number.isInteger(i)) {
                    state.items.splice(i, 1);
                    render();
                }
            });

            // Limpiar todo
            qs('#btnLimpiar').addEventListener('click', () => {
                state.items = [];
                render();
                msg.textContent = '';
            });

            // Guardar preventa (placeholder)
            qs('#btnGuardar').addEventListener('click', async () => {
                if (state.items.length === 0) {
                    msg.textContent = 'Agrega al menos un renglón.';
                    return;
                }
                // Aquí puedes enviar state.items con id, cantidad y pesoUnit si quieres persistir peso
                // fetch(API, { method:'POST', body: JSON.stringify({ items: state.items }), ... })

                msg.textContent = '✅ Pedido enviado';
                msg.style.color = '#1e8449';

                state.items = [];
                render();
                qs('#uv').value = '';
                selectArt.value = '';
                qs('#cantidad').value = '';
                qs('#obs').value = '';
                selectArt.focus();

                setTimeout(() => {
                    msg.textContent = '';
                    msg.style.color = '';
                }, 2500);
            });

            // --- filtro por línea (tu código actual) ---
            const norm = s => (s || '').toString().normalize('NFD').replace(/\p{Diacritic}/gu, '').toLowerCase().trim();
            const contBotones = document.getElementById('segLineas');
            const options = Array.from(selectArt.options);

            function filtraSelect(linea) {
                const objetivo = norm(linea);
                options.forEach(opt => {
                    if (opt.value === '') {
                        opt.hidden = false;
                        return;
                    }
                    const linOpt = norm(opt.dataset.linea || '');
                    opt.hidden = (objetivo !== 'all' && linOpt !== objetivo);
                });
                selectArt.value = '';
            }
            contBotones.addEventListener('click', e => {
                const btn = e.target.closest('.seg-btn');
                if (!btn) return;
                document.querySelectorAll('#segLineas .seg-btn').forEach(b => b.classList.toggle('is-active', b === btn));
                filtraSelect(btn.dataset.linea || 'all');
            });
            filtraSelect('all');

            render();
        })();
    </script>

    <?php include __DIR__ . '/layout/footer.php'; ?>