<!DOCTYPE html>
<html>
<head>
    <title>Gestión de Eventos</title>
    <!-- Incluimos Bootstrap para un diseño rápido y limpio -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1>Lista de Eventos</h1>

    <!-- Botón para crear un nuevo evento -->
    <a href="{{ route('eventos.create') }}" class="btn btn-primary mb-3">Crear Nuevo Evento</a>

    <!-- Mensaje de éxito (si existe) -->
    @if ($message = Session::get('success'))
        <div class="alert alert-success">
            <p>{{ $message }}</p>
        </div>
    @endif

    <table class="table table-bordered">
        <tr>
            <th>ID</th>
            <th>Fecha del Evento</th>
            <th>Descripción</th>
            <th width="280px">Acciones</th>
        </tr>
        @foreach ($eventos as $evento)
        <tr>
            <td>{{ $evento->id }}</td>
            <td>{{ $evento->fecha_evento }}</td>
            <td>{{ $evento->descripcion }}</td>
            <td>
                <form action="{{ route('eventos.destroy', $evento->id) }}" method="POST">
                    <a class="btn btn-info" href="{{ route('eventos.edit', $evento->id) }}">Editar</a>
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
            </td>
        </tr>
        @endforeach
    </table>
</div>
</body>
</html>