<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Evento') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                            <strong class="font-bold">¡Ups!</strong>
                             <ul class="list-disc mt-2 ml-4">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('eventos.update', $evento->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <!-- Fecha del Evento -->
                        <div>
                            <x-input-label for="fecha_evento" :value="__('Fecha del Evento')" />
                            <x-text-input id="fecha_evento" class="block mt-1 w-full" type="date" name="fecha_evento" :value="old('fecha_evento', $evento->fecha_evento)" required autofocus />
                        </div>

                        <!-- Descripción -->
                        <div class="mt-4">
                            <x-input-label for="descripcion" :value="__('Descripción')" />
                            <textarea id="descripcion" name="descripcion" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" rows="4">{{ old('descripcion', $evento->descripcion) }}</textarea>
                        </div>

                        <div class="mt-4">
                            <x-input-label for="precio_entrada" :value="__('Precio de la Entrada ($)')" />
                            <x-text-input id="precio_entrada" class="block mt-1 w-full" type="number" name="precio_entrada" :value="old('precio_entrada', $evento->precio_entrada)" step="0.01" min="0" />
                        </div>

                        
                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('eventos.index') }}" class="underline text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
                            <x-primary-button class="ms-4">
                                {{ __('Actualizar') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>