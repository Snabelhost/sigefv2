<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RefreshUserPermissions
{
    /**
     * Recarrega as permissões do usuário a cada request.
     * O cache global é limpo pelo Event Subscriber quando roles são alteradas.
     * Este middleware apenas garante que o modelo do usuário carrega dados frescos.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            
            // Limpa o cache interno do modelo de permissões
            // Isso força o Eloquent a buscar as relações novamente
            $user->unsetRelation('roles');
            $user->unsetRelation('permissions');
            
            // Recarrega as relações frescas do banco de dados
            $user->load('roles.permissions');
        }

        return $next($request);
    }
}


