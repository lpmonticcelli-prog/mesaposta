<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\User;

class ProfileController extends Controller
{
    /**
     * Exibe a tela da Diretoria (Perfil do Usuário Logado + Lista de toda a Equipe)
     */
    public function edit(Request $request): View
    {
        $users = User::orderBy('name')->get();
        return view('profile.edit', [
            'user' => $request->user(), 
            'users' => $users
        ]);
    }

    /**
     * Atualiza o Nome e o E-mail do usuário atual logado
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($request->user()->id)],
        ]);

        $user = $request->user();
        $user->name = $request->name;
        $user->email = $request->email;

        if ($user->isDirty('email')) { 
            $user->email_verified_at = null; 
        }
        
        $user->save();
        
        return Redirect::route('profile.edit')->with('success', 'Seus dados foram atualizados com sucesso.');
    }

    /**
     * Exclui a própria conta (O botão vermelho de "Explodir Conta")
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password']
        ]);
        
        $user = $request->user();
        
        Auth::logout();
        $user->delete();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return Redirect::to('/');
    }

    // =========================================================================
    // CADASTRO BLINDADO (Com Injeção da Patente / Nível de Acesso)
    // =========================================================================
    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'nivel_acesso' => 'required' // <-- Valida se a patente foi enviada
        ]);

        User::create([
            'name' => mb_strtoupper($request->name),
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'nivel_acesso' => $request->nivel_acesso // <-- Salva a patente no banco
        ]);
        
        return back()->with('success', 'Acesso da equipe cadastrado com a patente selecionada!');
    }

    /**
     * Revoga o acesso (Exclui) um usuário específico do sistema
     */
    public function destroyUser(User $user)
    {
        if(Auth::id() === $user->id) {
            return back()->with('error', 'Segurança: Você não pode excluir a si mesmo por este botão.');
        }
        
        $user->delete();
        
        return back()->with('success', 'Acesso revogado. Usuário removido do sistema.');
    }
}