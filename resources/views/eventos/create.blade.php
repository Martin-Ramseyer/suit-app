<!DOCTYPE html>
<html>
<head>
    <title>Crear Evento</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Crear Nuevo Evento</h2>

    <!-- Muestra errores de validación -->
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>¡Ups!</strong> Hubo algunos problemas con tu entrada.<br><br>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('eventos.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="fecha_evento" class="form-label">Fecha del Evento:</label>
            <input type="date" name="fecha_evento" class="form-control" placeholder="Fecha del Evento">
        </div>
        <div class="mb-3">
            <label for="descripcion" class="form-label">Descripción:</label>
            <textarea class="form-control" style="height:150px" name="descripcion" placeholder="Descripción"></textarea>
        </div>
        <div class="text-center">
            <button type="submit" class="btn btn-primary">Guardar</button>
            <a class="btn btn-secondary" href="{{ route('eventos.index') }}"> Cancelar</a>
        </div>
    </form>
</div>
</body>
</html>