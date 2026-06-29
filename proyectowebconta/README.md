# FinPlan AI - Planificación Financiera Personal con ML

Aplicación web de planificación financiera personal que utiliza **Aprendizaje Automático** para analizar ingresos y gastos, sugerir presupuestos y metas de ahorro.

## Tecnologías

- **PHP** (backend y motor ML)
- **MySQL** (base de datos via XAMPP)
- **HTML / CSS / JavaScript** (frontend)
- **Chart.js** (gráficos)
- **Font Awesome** (iconos)

## Requisitos

- [XAMPP](https://www.apachefriends.org/) con Apache y MySQL activos
- PHP 7.4 o superior

## Instalación

1. Copia la carpeta `proyectowebconta` dentro de `C:\xampp\htdocs\`

2. Inicia **Apache** y **MySQL** desde el panel de XAMPP

3. Abre en el navegador:
   ```
   http://localhost/proyectowebconta/install.php
   ```

4. Una vez instalado, accede a:
   ```
   http://localhost/proyectowebconta/login.php
   ```

5. **Credenciales demo:**
   - Email: `demo@finplan.com`
   - Contraseña: `demo123`

## Funcionalidades

| Módulo | Descripción |
|--------|-------------|
| **Dashboard** | Resumen financiero con gráficos de ingresos/gastos |
| **Transacciones** | Registrar ingresos y gastos por categoría |
| **Presupuesto** | Presupuesto sugerido por categoría con ML |
| **Metas de Ahorro** | Crear metas, abonar y seguir progreso |
| **Análisis ML** | Motor de aprendizaje automático completo |

## Algoritmos de Aprendizaje Automático

1. **Regresión Lineal Simple** - Predice tendencias de ingresos/gastos futuros
2. **Media Móvil Ponderada** - Estima valores basándose en meses recientes
3. **Regla 50/30/20 Adaptativa** - Clasifica gastos y sugiere presupuestos

## Estructura del Proyecto

```
proyectowebconta/
├── api/                  # Endpoints REST (JSON)
│   ├── auth.php
│   ├── transacciones.php
│   ├── analisis.php
│   └── metas.php
├── assets/
│   ├── css/style.css
│   └── js/app.js
├── config/
│   ├── database.php
│   └── session.php
├── database/
│   └── schema.sql
├── includes/
│   ├── ml_engine.php     # Motor de ML
│   ├── header.php
│   └── footer.php
├── index.php             # Dashboard
├── login.php
├── transacciones.php
├── presupuesto.php
├── metas.php
├── analisis.php
└── install.php
```

## Para la Presentación / Defensa

1. Mostrar login con usuario demo
2. Dashboard con gráficos y resumen
3. Registrar una transacción nueva
4. Ir a **Análisis ML** → Ejecutar análisis
5. Explicar los 3 algoritmos y las sugerencias generadas
6. Mostrar presupuesto sugerido por categoría
7. Mostrar metas de ahorro con proyecciones ML

## Notas

- Proyecto académico para presentación y defensa
- Los datos demo incluyen 6 meses de transacciones de ejemplo
- Eliminar `install.php` después de la instalación
