<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Panel de Administración') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (isset($ultimoEvento))
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-800 mb-2">
                            Último Evento: {{ \Carbon\Carbon::parse($ultimoEvento->fecha_evento)->format('d/m/Y') }}
                        </h3>
                        <p class="text-sm text-gray-600 mb-4">{{ $ultimoEvento->descripcion }}</p>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <h4 class="text-sm font-medium text-blue-800 uppercase">Invitados Totales</h4>
                                <p class="mt-1 text-3xl font-semibold text-blue-900">
                                    {{ $metricasUltimoEvento['totalInvitados'] ?? 'N/A' }}</p>
                            </div>
                            <div class="bg-green-50 p-4 rounded-lg">
                                <h4 class="text-sm font-medium text-green-800 uppercase">Ingresaron</h4>
                                <p class="mt-1 text-3xl font-semibold text-green-900">
                                    {{ $metricasUltimoEvento['invitadosIngresaron'] ?? 'N/A' }}</p>
                            </div>
                            <div class="bg-yellow-50 p-4 rounded-lg">
                                <h4 class="text-sm font-medium text-yellow-800 uppercase">Top RRPP</h4>
                                <p class="mt-1 text-lg font-semibold text-yellow-900">
                                    {{ $metricasUltimoEvento['topRrpp'] ?? 'Sin datos' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <a href="{{ route('usuarios.index') }}"
                    class="bg-white p-6 rounded-lg shadow-sm text-center hover:shadow-lg transition-shadow duration-300">
                    <h4 class="font-bold text-lg">Gestionar Usuarios</h4>
                </a>
                <a href="{{ route('eventos.index') }}"
                    class="bg-white p-6 rounded-lg shadow-sm text-center hover:shadow-lg transition-shadow duration-300">
                    <h4 class="font-bold text-lg">Gestionar Eventos</h4>
                </a>
                <a href="{{ route('eventos.historial') }}"
                    class="bg-white p-6 rounded-lg shadow-sm text-center hover:shadow-lg transition-shadow duration-300">
                    <h4 class="font-bold text-lg">Historial de Eventos</h4>
                </a>
                <a href="{{ route('invitados.index') }}"
                    class="bg-white p-6 rounded-lg shadow-sm text-center hover:shadow-lg transition-shadow duration-300">
                    <h4 class="font-bold text-lg">Lista de Invitados</h4>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
