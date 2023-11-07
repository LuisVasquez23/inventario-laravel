<!doctype html>
<html lang="en">

<head>
    <title>Descargar - PDF</title>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    
    <!-- Bootstrap CSS v5.2.1 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-iYQeCzEYFbKjA/T2uDLTpkwGzCiq6soy8tYaI1GyVh/UjpbCx/TYkiZhlZB6+fzT" crossorigin="anonymous">
</head>
<style>
    body {
    font-family: 'Roboto', sans-serif;
    }

    @font-face {
        font-family: 'Roboto';
        src: url('/fonts/Roboto/Roboto-Regular.ttf') format('truetype');
    }

    .position_img{
        position: absolute;
        top: -25px;     
    }
    .factura-position{
        position: relative;
        top: -90px; 
        left: 420px
    }

    .mi-linea {
        background-color: #333;
    }

    .factura {
        padding: 10px;
        margin: 10px;
        border-radius: 5px;
        height: 120px;
    }
        
    .factura h2 {
        font-size: 24px;
        margin: 0;
    }

    .datos {
        margin: 10px 0;
    }

    .datos p {
        margin: 2;
    }
    .postPosition{
        position: relative;      
        left: 50px
    }

    .table-header {
        background-color: #ebebeb; /* Color de fondo */
        color: #333; /* Color de texto */
        font-weight: bold; /* Hace que el texto sea negrita */
    }
    th, td{
        padding: 10px
    }
    #miTabla {
        border-bottom: 1px solid #000 !important;
    }
    .texto-grande {
        font-size: 18px; /* Ajusta el tamaño del texto según tus preferencias */
    }
</style>

<body>
    <div class="row align-items-center" style="height: 65px">
        <div class="col text-start">
            @foreach ($resultados3 as $resultado3)           
                <h2>{{ $resultado3->valor }}</h2>                
            @endforeach
        </div>
    
        <div class="col text-end d-flex justify-content-center position_img">
            <img src="data:image/jpeg;base64, {{ $resultados4 }}" alt="Imagen_logo" class="mx-auto"
                style="max-width: 250px; max-height: 100px;">
        </div>
    </div>
    
    <hr class="mi-linea ">
   
    <div class="factura row">
        <h3>Reporte de venta: </h3>
        <div class="col-md-6 datos postPosition">
            @foreach ($resultados1 as $resultado)
                <p><strong>Cliente:</strong> {{ $resultado->nombres }}  {{ $resultado->apellidos }} </p>
                <p><strong>Realizada por:</strong> {{ $resultado->creado_por }}</p>
            @endforeach
        </div>
        <div class="factura-position col-md-6 datos">
                     
            @foreach ($resultados1 as $resultado)
                <h3>N° Factura: <span style="color: #7b2c38">{{ $resultado->numerosfactura }}</span></h3>
                <div class="postPosition">
                    <p><strong>Periodo:</strong> {{ date('Y/m/d', strtotime($resultado->fecha_inicio)) }}</p>
                </div>
            @endforeach
        </div>
   </div>

    <div class="table-responsive">
        <table id="miTabla" class="table text-nowrap mb-0 align-middle table-striped table-bordered text-center">
            <thead class="text-dark fs-4 table-header">
                <tr>
                    <th class="border-bottom-0 text-uppercase font-weight-bold" style="width: 20%">
                        Producto
                    </th>
                    <th class "border-bottom-0 text-uppercase font-weight-bold">
                        Cantidad
                    </th>
                    <th class "border-bottom-0 text-uppercase font-weight-bold">
                        P Unitario
                    </th>
                    <th class "border-bottom-0 text-uppercase font-weight-bold">
                        Total
                    </th>
                    <th class "border-bottom-0 text-uppercase font-weight-bold">
                        Iva
                    </th>
                    <th class "border-bottom-0 text-uppercase font-weight-bold">
                        Total + Iva
                    </th>
                </tr>
            </thead>
            
            <tbody>
                @foreach ($resultados2 as $resultado)
                <tr>
                
                    <td class="border-bottom-0">
                        {{ mb_strimwidth(ucfirst($resultado->nombre), 0, 20, '...') }}
                    </td>
                    
                    <td>{{ $resultado->cantidad }}</td>
                    <td>${{ $resultado->precio }}</td>
                    <td>${{ $resultado->total }}</td>
                    <td>${{ number_format($resultado->Iva, 2) }}</td>
                    <td>${{ number_format($resultado->TotalConIva, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="text-end p-2">
            <p class="m-0 texto-grande"><strong>Total en venta con Iva:</strong> ${{ number_format($resultados5, 2) }}</p>
        
            <p class="texto-grande"><strong>Total en venta:</strong> ${{ $totalVenta->total }}</p>


        </div>
        
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"
        integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous">
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.min.js"
        integrity="sha384-7VPbUDkoPSGFnVtYi0QogXtr74QeVeeIs99Qfg5YCF+TidwNdjvaKZX19NZ/e6oz" crossorigin="anonymous">
    </script>
</body>

</html>
