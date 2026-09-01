@if ($paginator->hasPages())
    <nav aria-label="Paginação de atividades">
        <ul class="admin-paginacao__lista">
            @if ($paginator->onFirstPage())
                <li><span class="admin-paginacao__link admin-paginacao__link--desabilitado" aria-disabled="true">Anterior</span></li>
            @else
                <li><a class="admin-paginacao__link" href="{{ $paginator->previousPageUrl() }}" rel="prev">Anterior</a></li>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li><span class="admin-paginacao__reticencias" aria-hidden="true">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $pagina => $url)
                        @if ($pagina === $paginator->currentPage())
                            <li><span class="admin-paginacao__link admin-paginacao__link--atual" aria-current="page">{{ $pagina }}</span></li>
                        @else
                            <li><a class="admin-paginacao__link" href="{{ $url }}" aria-label="Ir para página {{ $pagina }}">{{ $pagina }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <li><a class="admin-paginacao__link" href="{{ $paginator->nextPageUrl() }}" rel="next">Próxima</a></li>
            @else
                <li><span class="admin-paginacao__link admin-paginacao__link--desabilitado" aria-disabled="true">Próxima</span></li>
            @endif
        </ul>
    </nav>
@endif
