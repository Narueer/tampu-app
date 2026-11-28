{{-- recuadro con borde de color a la izquierda; $color cambia el tono --}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:22px 0 4px;">
<tr><td style="background:#F5F3ED; border-left:4px solid {{ $color ?? '#965c57' }}; border-radius:0 10px 10px 0; padding:14px 18px;">
    @isset($titulo)
        <p style="margin:0 0 4px; font-family:'Helvetica Neue', Helvetica, Arial, sans-serif; font-size:14px; font-weight:bold; color:#343936;">{{ $titulo }}</p>
    @endisset
    <p style="margin:0; font-family:'Helvetica Neue', Helvetica, Arial, sans-serif; font-size:14px; line-height:21px; color:#4f5650;">{!! $html !!}</p>
</td></tr>
</table>
