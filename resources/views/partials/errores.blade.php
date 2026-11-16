@if ($errors->any())
    <div class="alerta alerta-error">
        <b>Revisa lo siguiente:</b>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
