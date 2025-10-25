<?php
$title = "Preventa - Captura";
include __DIR__ . '/../app/appPedidos.php'; 
include __DIR__ . '/layout/header.php';

// Puedes tomar datos de sesión para prefijar ruta/UV/etc.
$idRuta = $_SESSION['IdRuta'] ?? 0;
?>
<link rel="stylesheet" href="../tools/newStyle.css">

<main class="page" style="padding:18px;">
    <header class="page-head" style="max-width:1100px;margin:0 auto 14px;">
        <h1 class="page-title" style="margin:6px 0 4px; font-weight:800;">PEDIDO DE CARGA</h1>
        <p class="page-sub" style="margin:0;color:#8aa2b4;">Ruta <?= (int)$idRuta ?> · agrega artículos y cantidades</p>
    </header>

    <!-- Cabecera de preventa (versión móvil mejorada) -->
    <section class="card" style="max-width:600px;margin:0 auto 16px;padding:16px;font-size:1.1rem;">
        <form id="frmCab" class="grid" style="display:flex;flex-direction:column;gap:14px;">
            <input type="hidden" id="idRuta" value="<?= (int)$idRuta ?>">

            <!-- Unidad de venta 
            <div>
                <label class="lbl" for="uv" style="display:block;font-weight:700;margin-bottom:6px;">Unidad de venta</label>
                <select class="in" id="uv" style="width:100%;padding:12px;font-size:1.05rem;border-radius:8px;">
                    <option value="">Selecciona una unidad de venta</option>
                    <option value="1">UV101 - Tijuana</option>
                    <option value="2">UV102 - Tijuana</option>
                    <option value="3">UV103 - Tijuana</option>
                    <option value="4">UV104 - Tijuana</option>
                    <option value="5">UV105 - Tijuana</option>
                    <option value="6">UV106 - Tijuana</option>
                    <option value="7">UV107 - Tijuana</option>
                    <option value="8">UV108 - Tijuana</option>
                    <option value="9">UV109 - Tijuana</option>
                    <option value="10">UV110 - Tijuana</option>
                    <option value="11">UV113 - Tijuana</option>
                    <option value="12">UV114 - Tijuana</option>
                    <option value="13">UV115 - Tijuana</option>
                    <option value="14">UV116 - Tijuana</option>
                    <option value="15">UV117 - Tijuana</option>
                    <option value="16">UV118 - Tijuana</option>
                    <option value="17">UV119 - Tijuana</option>
                    <option value="18">UV120 - Tijuana</option>
                    <option value="19">UV121 - Tijuana</option>
                    <option value="20">UV122 - Tijuana</option>
                </select>
            </div>-->
            
        </form>
    </section>

    <!-- Capturador de renglones -->
    <section class="card" style="max-width:600px;margin:0 auto;padding:16px;font-size:1.05rem;">
        <h2 style="margin:0 0 14px;font-size:1.3rem;font-weight:800;">Agregar artículo</h2>

        <div class="grid" style="display:grid;grid-template-columns:1fr 0.6fr auto;gap:12px;align-items:end;">
            <div class="seg-lineas" id="segLineas" style="margin:6px 0 10px;">
                <button type="button" class="seg-btn is-active" data-linea="all">Todos</button>
                <button type="button" class="seg-btn" data-linea="embutidos">Embutidos</button>
                <button type="button" class="seg-btn" data-linea="carnes">Carnes frías</button>
                <button type="button" class="seg-btn" data-linea="queso">Queso</button>
                <button type="button" class="seg-btn" data-linea="manteca">Manteca</button>
            </div>
            <div>
                <label class="lbl" for="articulo" style="font-weight:700;">Artículo</label>
                <select class="in" id="articulo" style="width:100%;padding:12px;font-size:1.05rem;border-radius:8px;">
                    <option value="">Selecciona un artículo</option>

                    <!-- Empaques / varios -->
                    <option value="1001" data-line="embutidos">1001 - BOL 200 GR</option>
                    <option value="1002" data-line="embutidos">1002 - BOL TW 1 KG</option>
                    <option value="1003" data-line="embutidos">1003 - BOL 2 KG</option>
                    <option value="1005" data-line="embutidos">1005 - BOL 4.6 KG</option>
                    <option value="1111" data-line="otros">1111 - CHAROLA</option>
                    <option value="1122" data-line="otros">1122 - CANASTILLA</option>
                    <option value="1123" data-line="otros">1123 - TARIMA</option>

                    <!-- Embutidos (salchichas, chorizo) -->
                    <option value="2001" data-line="embutidos">2001 - SAL RES 400</option>
                    <option value="2006" data-line="embutidos">2006 - SAL RES GRA 2 KG</option>
                    <option value="2007" data-line="embutidos">2007 - SALCH RES J 1.8 KG</option>
                    <option value="2010" data-line="embutidos">2010 - SALCH HDOG 200 GR</option>
                    <option value="2011" data-line="embutidos">2011 - SALCH HD GRANEL 2 KG</option>
                    <option value="2013" data-line="embutidos">2013 - SALCH HDOG 1.125 KG</option>
                    <option value="2014" data-line="embutidos">2014 - SALCH DOGUERA SIN TC 1.08KG</option>
                    <option value="2015" data-line="embutidos">2015 - SALCH PAV340 GR</option>
                    <option value="2016" data-line="embutidos">2016 - SALCH PAV G2 KG</option>
                    <option value="2099" data-line="embutidos">2099 - SALCHICHA ECONOMICA</option>
                    <option value="2100" data-line="embutidos">2100 - SALCHICHA ECONOMICA 200 GR</option>
                    <option value="2101" data-line="embutidos">2101 - SALCHICHA PARA ASAR 400 GR</option>

                    <!-- Carnes frías (jamón, tocino, lomo) -->
                    <option value="3005" data-line="carnes">3005 - JAMON PIERNA 200 GR</option>
                    <option value="3006" data-line="carnes">3006 - JAMON PIERNA TW 1 KG</option>
                    <option value="3007" data-line="carnes">3007 - JAMON PIERNA 5.2 KG</option>
                    <option value="3008" data-line="carnes">3008 - JAMON PIERNA 2.5 KG</option>
                    <option value="3010" data-line="carnes">3010 - LOMO CDO 6.8 KG</option>
                    <option value="3012" data-line="carnes">3012 - JAMON NAV 3 KG</option>
                    <option value="3017" data-line="carnes">3017 - JAMON VIR 200 GR</option>
                    <option value="3018" data-line="carnes">3018 - JAMON VIR TW 1 KG</option>
                    <option value="3019" data-line="carnes">3019 - JAMON VIR 5.2 KG</option>
                    <option value="4001" data-line="carnes">4001 - TOCINO 200 GR</option>
                    <option value="4004" data-line="carnes">4004 - TOCINO REB 1 KG</option>
                    <option value="4005" data-line="carnes">4005 - TOCINO 7 KG</option>
                    <option value="4008" data-line="carnes">4008 - TOCINO ENT 6.8 KG</option>
                    <option value="4010" data-line="carnes">4010 - TOCINO REC 2 KG</option>
                    <option value="4011" data-line="carnes">4011 - TOCINO EN TROZOS 20 KG</option>
                    <option value="4012" data-line="carnes">4012 - TOCREB B 1KG</option>
                    <option value="4013" data-line="carnes">4013 - TOCINO ENT B 6.8 KG</option>

                    <!-- Manteca / grasas -->
                    <option value="5501" data-line="manteca">5501 - MANTECA 1 KG</option>
                    <option value="5502" data-line="manteca">5502 - MANTECA 20 KG</option>
                    <option value="5503" data-line="manteca">5503 - MANTECA BOL 20KG</option>
                    <option value="5504" data-line="manteca">5504 - MANTECA BOL 22KG</option>
                    <option value="5505" data-line="manteca">5505 - MANTECA 400 GR</option>
                    <option value="5506" data-line="manteca">5506 - MANTECA 800</option>
                    <option value="5507" data-line="manteca">5507 - MANTECA BOL 22.7</option>
                    <option value="5508" data-line="manteca">5508 - MANTECA 16 KG</option>
                    <option value="5509" data-line="manteca">5509 - MTK DORADA 400 GR</option>
                    <option value="5510" data-line="manteca">5510 - MTK DORADA 800 GR</option>
                    <option value="5511" data-line="manteca">5511 - MTK DORADA 16 KG</option>
                    <option value="7101" data-line="manteca">7101 - IBERIA MARGARINA 400G</option>
                    <option value="7102" data-line="manteca">7102 - IBERIA NORTENA 90G</option>
                    <option value="7201" data-line="manteca">7201 - PRIMAV MARGARINA 360G</option>
                    <option value="7202" data-line="manteca">7202 - PRIMAV CHANTILLY 190G</option>

                    <!-- Quesos / aderezos -->
                    <option value="8001" data-line="queso">8001 - QUESO MANCHEGO 300 GR</option>
                    <option value="8002" data-line="queso">8002 - QUESO MOZARELLA 300 GR</option>
                    <option value="8003" data-line="queso">8003 - QUESO CHIH 300 GR</option>
                    <option value="8006" data-line="queso">8006 - QUESO MOZ M B 2.27 KG</option>
                    <option value="8007" data-line="queso">8007 - QUESO CHIH M B 2.27 KG</option>
                    <option value="8009" data-line="queso">8009 - QUESO MAN M B 2.27 KG</option>
                    <option value="8015" data-line="queso">8015 - QUESO MTY J 300 GR</option>
                    <option value="8016" data-line="queso">8016 - QUESO MTYJACK MB 2.27 KG</option>
                    <option value="8027" data-line="queso">8027 - QUESO AM ROS 140 GR</option>
                    <option value="8030" data-line="queso">8030 - ADEREZO PARA NACHOS 1 KG</option>
                    <option value="8031" data-line="queso">8031 - QUESO COTIJA 1 KG</option>

                    <!-- Embutidos (chorizos) -->
                    <option value="9002" data-line="embutidos">9002 - CHORIZO 250 GR</option>
                    <option value="9003" data-line="embutidos">9003 - CHORIZO RANCHERO 250 GR</option>
                    <option value="9099" data-line="embutidos">9099 - CHORIZO SOYA 250 GR</option>

                    <!-- Combo -->
                    <option value="94008" data-line="otros">94008 - COMBO M1</option>

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
                    <tr style="font-size:1.05rem;">
                        <th style="text-align:left;padding:10px;">Artículo</th>
                        <th style="text-align:left;padding:10px;">Cantidad</th>
                        <th style="text-align:left;padding:10px;">Acciones</th>
                    </tr>
                </thead>
                <tbody id="rows">
                    <tr>
                        <td colspan="3" style="padding:14px;">Sin renglones</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <footer style="display:flex;gap:10px;justify-content:space-between;align-items:center;margin-top:14px;flex-wrap:wrap;">
            <div class="muted" style="font-size:1rem;"><span id="count">0</span> renglón(es)</div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <button id="btnLimpiar" class="btn-cancelar" type="button" style="padding:12px 18px;font-size:1rem;">Limpiar</button>
                <button id="btnGuardar" class="app-btnCap" type="button" style="padding:12px 18px;font-size:1rem;">Guardar preventa</button>
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
            const API = '../app/appPedidos.php'; // lo seguimos usando para GuardarPreventa
            const qs = s => document.querySelector(s);
            const rows = qs('#rows');
            const msg = qs('#msg');

            const state = {
                items: []
            }; // { id, nombre, cantidad }

            // Helpers
            const e = v => (v ?? '').toString()
                .replaceAll('&', '&amp;').replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;').replaceAll('"', '&quot;').replaceAll("'", '&#039;');

            function render() {
                if (state.items.length === 0) {
                    rows.innerHTML = `<tr><td colspan="3" style="padding:12px;">Sin renglones</td></tr>`;
                    return;
                }
                rows.innerHTML = state.items.map((it, i) => `
  <tr>
    <td style="padding:5px;">${e(it.nombre)}</td>
    <td style="padding:5px;">
      <input type="text" inputmode="decimal" pattern="[0-9]*[.,]?[0-9]*"
             value="${e(it.cantidad)}" data-i="${i}"
             class="in qty-input qty" autocomplete="off" enterkeyhint="done">
    </td>
    <td style="padding:5px;">
      <button class="btn-cancelar btnDel btn-sm" data-i="${i}" type="button">Eliminar</button>
    </td>
  </tr>
`).join('');

                qs('#count').textContent = state.items.length;
            }

            // AGREGAR RENGLÓN (combo de artículos + cantidad)
            qs('#btnAgregar').addEventListener('click', () => {
                const artSel = qs('#articulo');
                const id = parseInt(artSel.value || '0', 10);
                const nombre = artSel.options[artSel.selectedIndex]?.text?.trim() || '';
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

                // Si ya existe el artículo en la lista, sumar cantidades
                const dup = state.items.find(i => i.id === id);
                if (dup) dup.cantidad = +(dup.cantidad) + cantidad;
                else state.items.push({
                    id,
                    nombre,
                    cantidad
                });

                // limpiar y enfocar para captura rápida en cel
                qs('#cantidad').value = '';
                msg.textContent = '';
                render();
                qs('#articulo').focus();
            });

            // Editar cantidad en la tabla
            rows.addEventListener('input', ev => {
                if (ev.target.classList.contains('qty')) {
                    const i = +ev.target.dataset.i;
                    let v = parseFloat(ev.target.value);
                    if (!isFinite(v) || v < 0) v = 0;
                    state.items[i].cantidad = v;
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

            // Guardar preventa (envía lo que capturaste)
            qs('#btnGuardar').addEventListener('click', async () => {
                if (state.items.length === 0) {
                    msg.textContent = 'Agrega al menos un renglón.';
                    return;
                }

                msg.textContent = '✅ Pedido enviado';
                msg.style.color = '#1e8449'; // verde éxito

                // Limpiar todos los datos capturados
                state.items = [];
                render(); // esto limpia la tabla (rows)

                // Limpiar campos de formulario
                qs('#uv').value = '';
                qs('#articulo').value = '';
                qs('#cantidad').value = '';
                qs('#obs').value = '';

                // Enfocar de nuevo el primer campo para seguir capturando
                qs('#articulo').focus();

                // Borrar el mensaje después de unos segundos
                setTimeout(() => {
                    msg.textContent = '';
                    msg.style.color = '';
                }, 2500);

            });

            // primer render
            render();
            // --- Filtro por línea ---
            const seg = document.getElementById('segLineas');
            const sel = document.getElementById('articulo');

            // Guardamos una copia de TODAS las opciones (excepto el placeholder)
            const allOptions = [...sel.querySelectorAll('option')].map(o => o.cloneNode(true));

            function aplicarFiltro(linea) {
                // limpiar manteniendo placeholder
                const placeholder = sel.querySelector('option[value=""]')?.cloneNode(true);
                sel.innerHTML = '';
                if (placeholder) sel.appendChild(placeholder);

                let opts = allOptions.slice(1); // sin placeholder

                if (linea && linea !== 'all') {
                    opts = opts.filter(o => {
                        const tag = (o.dataset.line || '').toLowerCase();
                        if (tag) return tag === linea; // Usa data-line si existe
                        // Fallback: intenta inferir por prefijo numérico del texto (primer dígito)
                        const code = (o.textContent || '').trim();
                        const first = code[0];
                        const mapFallback = {
                            '1': 'embutidos',
                            '2': 'carnes',
                            '3': 'queso',
                            '4': 'manteca',
                            '5': 'otros'
                        };
                        return (mapFallback[first] || '') === linea;
                    });
                }
                // volver a inyectar
                opts.forEach(o => sel.appendChild(o.cloneNode(true)));

                // si la opción actualmente seleccionada desapareció, resetea
                if (!sel.querySelector(`option[value="${sel.value}"]`)) {
                    sel.value = '';
                }
            }

            // Click en botones (toggle de activo y filtro)
            seg.addEventListener('click', (ev) => {
                const btn = ev.target.closest('.seg-btn');
                if (!btn) return;
                [...seg.querySelectorAll('.seg-btn')].forEach(b => b.classList.remove('is-active'));
                btn.classList.add('is-active');
                aplicarFiltro(btn.dataset.linea);
            });

            // Inicial: "Todos"
            aplicarFiltro('all');
        })();
    </script>


    <?php include __DIR__ . '/layout/footer.php'; ?>