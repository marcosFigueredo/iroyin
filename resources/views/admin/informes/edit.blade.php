<x-app-layout>
    <x-slot name="header">Editar informe</x-slot>
    <form method="POST" action="{{ route('admin.informes.update', $informe) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.informes._form')
    </form>
</x-app-layout>
