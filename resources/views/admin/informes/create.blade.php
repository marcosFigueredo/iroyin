<x-app-layout>
    <x-slot name="header">Novo informe</x-slot>
    <form method="POST" action="{{ route('admin.informes.store') }}" enctype="multipart/form-data">
        @csrf
        
        @include('admin.informes._form')
    </form>
</x-app-layout>
