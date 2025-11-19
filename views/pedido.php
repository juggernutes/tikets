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
$registros = 0;
$volumenTotal = 0;

?>
<link rel="stylesheet" href="../tools/newStyle.css">

<input type="hidden" id="IDUO" value="<?= (int)($unidad[0]['IDUO'] ?? 0) ?>">
<input type="hidden" id="iduser" value="<?= (int)($_SESSION['login_id'] ?? 0) ?>">
<input type="hidden" id="ID_CAPUV" value="<?= (int)($unidad[0]['ID_CAPUV'] ?? 0) ?>">
<input type="hidden" id="ID_SUPERVISOR_UO" value="<?= (int)($unidad[0]['ID_SUPERVISOR_UO'] ?? 0) ?>">
<input type="hidden" id="ID_ALMACEN_UO" value="<?= (int)($unidad[0]['ID_ALMACEN_UO'] ?? 0) ?>">

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
        <tfoot>
            <tr>
                <td colspan="2"><strong>Renglones: <span id="rowCount">0</span></strong></td>
                <td colspan="3" style="text-align:right;"><strong>Total: <span id="totalKg">0.000</span> kg</strong></td>
            </tr>
        </tfoot>

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
                        <td colspan="5" style="padding:14px;">Sin renglones</td>
                    </tr>
                </tbody>

                
            </table>
        </div>
        <div id="msg" class="muted" style="margin-top:10px;font-size:2rem;"></div>
        <div class="section-sep"></div>
        <div>
            <tr>
                <td colspan="3" style="text-align:right;"><strong>Total: <span id="totalKg2">0.000</span> kg</strong></td>
            </tr>
        </div>

        <div class="section-sep"></div>
        <div>
            <label class="lbl" for="modelo" style="display:block;font-weight:700;margin-bottom:6px;">Modelo - <?php echo $carro; ?> </label>
            <label class="lbl" for="peso" style="display:block;font-weight:700;margin-bottom:6px;">Carga máxima / mínima: <?php echo $pesoMaximo; ?> / <?php echo $pesoMinimo; ?> kg</label>
        </div>

        <footer style="display:flex;gap:10px;justify-content:space-between;align-items:center;margin-top:14px;flex-wrap:wrap;">
            <div class="muted" style="font-size:1rem;">
                <span id="rowCount2">0</span> renglón(es)
            </div>

            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <button id="btnLimpiar" class="btn-cancelar" type="button" style="padding:12px 18px;font-size:1rem;">Limpiar</button>
                <button id="btnGuardar" class="app-btnCap" type="button" style="padding:12px 18px;font-size:1rem;">Guardar pedido</button>
            </div>
        </footer>
        <section>
            <!-- Observaciones -->

            <div>
                <label class="lbl" for="obs" style="display:block;font-weight:700;margin-bottom:6px;">Observaciones</label>
                <textarea id="obs" class="in" placeholder="Ej. Aclaraciones necesarias…"
                    style="width:100%;min-height:100px;resize:vertical;padding:12px;font-size:1.05rem;border-radius:8px;">
                </textarea>
            </div>
        </section>
        
    </section>



    <script>
        (() => {
            const qs = s => document.querySelector(s);
            const rows = qs('#rows');
            const msg = qs('#msg');
            const selectArt = qs('#articulo');

            const state = {
                items: []
            }; // { id, nombre, cantidad, pesoUnit }

            const pesoMax = parseFloat('<?= (float)$pesoMaximo ?>') || 0;
            const pesoMin = parseFloat('<?= (float)$pesoMinimo ?>') || 0;

            const e = v => (v ?? '').toString()
                .replaceAll('&', '&amp;').replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;').replaceAll('"', '&quot;')
                .replaceAll("'", "&#039;");

            function calcTotales() {
                let totalKg = 0;
                const itemsConVol = state.items.map(it => {
                    const volArt = (Number(it.cantidad) || 0) * (Number(it.pesoUnit) || 0);
                    totalKg += volArt;
                    const totalKg2 = totalKg;
                    return {
                        ...it,
                        volArt
                    };
                });
                return {
                    itemsConVol,
                    totalKg,
                    totalKg2
                };
            }

            function render() {
                const {
                    itemsConVol,
                    totalKg
                } = calcTotales();

                if (itemsConVol.length === 0) {
                    rows.innerHTML = `<tr><td colspan="5" style="padding:12px;">Sin renglones</td></tr>`;
                    qs('#rowCount').textContent = itemsConVol.length;
                    qs('#rowCount2').textContent = itemsConVol.length;
                    qs('#totalKg').textContent = '0.000';
                    qs('#totalKg2').textContent = '0.000';
                    return;
                }

                rows.innerHTML = itemsConVol.map((it, i) => `
      <tr style="font-size:.75rem;">
        <td style="padding:4px;">${e(it.nombre)}</td>
        <td style="padding:4px;">
          <input type="text" inputmode="decimal" pattern="[0-9]*[.,]?[0-9]*"
                 value="${e(it.cantidad)}" data-i="${i}"
                 class="in qty" autocomplete="off" enterkeyhint="done" style="width: 60px;">
        </td>
        <td style="padding:4px;">${(+it.pesoUnit).toFixed(3)} kg/u</td>
        <td style="padding:4px;">${it.volArt.toFixed(3)} kg</td>
        <td style="padding:4px;">
          <button class="btn-cancelar btnDel btn-sm" data-i="${i}" type="button">X</button>
        </td>
      </tr>
    `).join('');

                qs('#rowCount').textContent = itemsConVol.length;
                qs('#rowCount2').textContent = itemsConVol.length;
                qs('#totalKg').textContent = totalKg.toFixed(3);
                qs('#totalKg2').textContent = totalKg.toFixed(3);
            }

            // Agregar renglón
            qs('#btnAgregar').addEventListener('click', () => {
                const artSel = selectArt;
                const id = parseInt(artSel.value || '0', 10);
                const nombre = artSel.options[artSel.selectedIndex]?.text?.trim() || '';
                const pesoUnit = parseFloat(artSel.options[artSel.selectedIndex]?.dataset?.peso || '0');
                const raw = qs('#cantidad').value.trim().replace(',', '.');
                const cantidad = parseFloat(raw);

                if (!id || !nombre) {
                    msg.textContent = 'Selecciona un artículo.';
                    return;
                }
                if (!isFinite(cantidad) || cantidad <= 0) {
                    msg.textContent = 'Indica una cantidad > 0.';
                    return;
                }
                if (!isFinite(pesoUnit) || pesoUnit <= 0) {
                    msg.textContent = 'El artículo no tiene peso válido.';
                    return;
                }

                const dup = state.items.find(i => i.id === id);
                if (dup) dup.cantidad = (+dup.cantidad) + cantidad;
                else state.items.push({
                    id,
                    nombre,
                    cantidad,
                    pesoUnit
                });

                qs('#cantidad').value = '';
                msg.textContent = '';
                render();
                selectArt.focus();
            });

            // Editar cantidad
            rows.addEventListener('input', ev => {
                if (!ev.target.classList.contains('qty')) return;
                const i = +ev.target.dataset.i;
                let v = parseFloat(ev.target.value.replace(',', '.'));
                if (!isFinite(v) || v < 0) v = 0;
                state.items[i].cantidad = v;
                render();
            });

            // Eliminar
            rows.addEventListener('click', ev => {
                const btn = ev.target.closest('.btnDel, .btn-cancelar');
                if (!btn) return;
                const i = +btn.dataset.i;
                if (Number.isInteger(i)) {
                    state.items.splice(i, 1);
                    render();
                }
            });

            // Limpiar
            qs('#btnLimpiar').addEventListener('click', () => {
                state.items = [];
                render();
                msg.textContent = '';
            });

            // Guardar: valida capacidad y envía
            qs('#btnGuardar').addEventListener('click', async () => {
                if (state.items.length === 0) {
                    msg.textContent = 'Agrega al menos un renglón.';
                    return;
                }

                const {
                    itemsConVol,
                    totalKg
                } = calcTotales();

                // Validación capacidad (opcional: bloquea o solo avisa)
                if (pesoMax > 0 && totalKg > pesoMax + 0.001) {
                    msg.textContent = `❌ Excede la capacidad máxima (${totalKg.toFixed(3)} kg > ${pesoMax.toFixed(3)} kg).`;
                    msg.style.color = '#b03a2e';
                    return;
                }
                if (pesoMin > 0 && totalKg < pesoMin - 0.001) {
                    msg.textContent = `⚠️ Por debajo de la carga mínima (${totalKg.toFixed(3)} kg < ${pesoMin.toFixed(3)} kg).`;
                    msg.style.color = '#b07d2e';
                    // Si deseas bloquear, agrega: return;
                }

                const header = {
                    ID_CAPUV: +qs('#ID_CAPUV').value || 0,
                    ID_UNIDAD: +qs('#IDUO').value || 0,
                    ID_SUPERVISOR_UO: +qs('#ID_SUPERVISOR_UO').value || 0,
                    ID_ALMACEN_UO: +qs('#ID_ALMACEN_UO').value || 0,
                    Registro: itemsConVol.length,
                    Volumen: +totalKg.toFixed(3),
                    Obser: (qs('#obs').value || '').trim(),
                    ID_USUARIO: +qs('#iduser').value || 0
                };

                const payload = {
                    header,
                    items: itemsConVol.map(it => ({
                        idArticulo: it.id,
                        cantidad: +(+it.cantidad).toFixed(3), // piezas
                        volArt: +(+it.volArt).toFixed(3), // kg
                        pesoUnit: +(+it.pesoUnit).toFixed(3) // kg/u (por si quieres persistirlo)
                    }))
                };

                try {
                    const r = await fetch('../app/appPedidos.php?action=guardar', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify(payload)
                    });

                    const text = await r.text(); // leemos texto
                    let out;

                    try {
                        out = JSON.parse(text);
                    } catch (e) {
                        throw new Error('Respuesta no es JSON válido: ' + text.substring(0, 120));
                    }

                    if (!r.ok || !out.ok) {
                        throw new Error(out.error || out.msg || 'Error al guardar');
                    }

                    const folio = out.Folio || out.folio || 'SIN_FOLIO';

                    // ✅ Mostrar folio generado
                    msg.textContent = `✅${folio} \nguardado correctamente`;
                    msg.style.color = '#1e8449';
                    msg.style.fontWeight = '600';

                    // ✅ Borrar registros del state y limpiar formulario
                    state.items = [];
                    render();
                    selectArt.value = '';
                    qs('#cantidad').value = '';
                    qs('#obs').value = '';

                    setTimeout(() => {
                        msg.textContent = '';
                        msg.style.color = '';
                    }, 2500);

                } catch (err) {
                    msg.textContent = `❌ ${err.message}`;
                    msg.style.color = '#b03a2e';
                }

            });

            // --- filtros por línea (tus botones ya sirven con normalización) ---
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