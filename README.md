<p align="center">
  <img src="./logo.svg" width="200" height="200" />
</p> 

# <center>Prueba Técnica Desarrollador Backend</center>
<p align="center">
  <img src="https://img.shields.io/badge/php-%23777BB4.svg?style=for-the-badge&logo=php&logoColor=white" />
  <img src="https://img.shields.io/badge/laravel-%23FF2D20.svg?style=for-the-badge&logo=laravel&logoColor=white" />
</p>
<hr>

## 🚀 Descripción

Esta prueba técnica consiste en consumir tres endpoints públicos que contienen datos relacionados con beneficios económicos, sus filtros de validación y sus respectivas fichas, para luego exponer un nuevo endpoint REST que devuelva estos datos filtrados y estructurados correctamente.

## 📦 Requisitos del endpoint

El endpoint `/api/beneficios-filtrados` debe:

1. Consumir los siguientes endpoints públicos:
   - **Beneficios:** contiene `id_programa`, `monto`, `fecha_recepcion`, `fecha`.
   - **Filtros:** contiene `id_programa`,`tramite`, `min`, `max`, `ficha_id`.
   - **Fichas:** contiene `id`, `nombre`, `id_programa`, `url`, `categoria`, `descripcion`.

2. Filtrar los beneficios que:
   - Tengan un `id_programa` válido en el filtro.
   - El `monto` esté entre `min` y `max` del filtro correspondiente.

4. Agrupar los beneficios por año (`ano` derivado de `fecha`) y devolver la siguiente estructura:

```json
{
  "year": 2023,
  "num": 5,
  "beneficios": [
    {
      "id_programa": 147,
      "monto": 40656,
      "fecha_recepcion": "09/11/2023",
      "fecha": "2023-11-09",
      "ano": "2023",
      "view": true,
      "ficha": {
        "id": 922,
        "nombre": "Emprende",
        "url": "emprende",
        "categoria": "trabajo",
        "descripcion": "..."
      }
    }
  ]
}
```

## 🛠️ Tecnologías utilizadas
- PHP 8+
- Laravel 11

## 📂 Estructura del proyecto
- `App\Http\Controllers\Api\BeneficioController`: orquesta el flujo completo.
- `App\Services\BeneficioService`: abstrae el proceso de filtrado, agrupado y formateo.

## ▶️ Cómo ejecutar

1. Clona el repositorio:
```bash
git clone https://github.com/F4bian-pacheco/beneficios_api_consumer
```

2. Instala las dependencias:
```bash
composer install
```

3. Ejecuta el servidor:
```bash
composer run dev
```

4. Accede al endpoint:
```
GET http://localhost:8000/api/beneficios-filtrados
```
