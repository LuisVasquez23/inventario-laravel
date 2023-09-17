<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\DetalleRole;
use Illuminate\Http\Request;
use PhpParser\Node\Stmt\TryCatch;
use App\Models\Usuario;
use App\Models\Role;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AdminEmpleadosController extends Controller
{
    public function index(Request $request)
    {

        try{


            $filtro = $request->input('filtro', 'no-bloqueados');

            if ($filtro === 'bloqueados') {
                $empleados = Usuario::whereHas('detalle_roles', function ($query) {
                    $query->whereHas('role', function ($innerQuery) {
                        $innerQuery->where('role', 'Empleado');
                    });
                })
                ->whereNotNull('bloqueado_por')
                ->get();
            } else {
                $empleados = Usuario::whereHas('detalle_roles', function ($query) {
                    $query->whereHas('role', function ($innerQuery) {
                        $innerQuery->where('role', 'Empleado');
                    });
                })
                ->whereNull('bloqueado_por')
                ->get();
            }

        return view('empleados.index', compact('empleados', 'filtro'));

        }catch(\Exception $e){
            
            Log::error($e->getMessage());
            return redirect()->route('dashboard')->with('error', 'Error al cargar la pagina de empleados');
        }  
       
    }

    public function create()
    {
        try {
            return view('empleados.create');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return redirect()->route('empleados.index')->with('error', 'Error al cargar la página para agregar un empleado');
        }
    }


    public function store(Request $request)
    {
        try {


            // Define las reglas de validación
            $rules = [
    
                'dui_opcion' => 'required|unique:usuarios,dui',
                'nombre_opcion' => 'required',
                'apellido_opcion' => 'required',
                'telefono_opcion' => 'required|regex:/^\d{4}-\d{4}$/|unique:usuarios,telefono',
                'direccion_opcion' => 'nullable',
                'email_opcion' => 'required|email|unique:usuarios,email',
            ];
    
            $messages = [
    
                'dui_opcion.required' => 'El campo "Dui" es obligatorio.',
                'dui_opcion.unique' => 'El DUI ingresado ya está registrado, intenta de nuevo.',
                'nombre_opcion.required' => 'Debes registrar al menos un nombre',
                'apellido_opcion.required' => 'Debes registrar al menos un apellido',
                'telefono_opcion.required' => 'El campo "Teléfono" es obligatorio.',
                'telefono_opcion.unique' => 'Este teléfono ya está registrado, intenta de nuevo.',
                'telefono_opcion.regex' => 'El campo "Teléfono" debe tener el formato correcto (por ejemplo, 7889-1256).',
                'email_opcion.required' => 'El correo es requerido',
                'email_opcion.unique' => 'El correo ya está registrado, intenta de nuevo',
                'email_opcion.email' => 'El campo "correo" debe ser una dirección de correo electrónico válida.',
    
            ];
    
            $validator = Validator::make($request->all(), $rules, $messages);
    
            if ($validator->fails()) {
                return redirect()
                    ->route('empleados.create') 
                    ->withErrors($validator)
                    ->withInput();
            }
    
    
            // Verificar si se seleccionó "Seleccionar..." en el campo "departamento"
            if ($request->input('departamento') === 'Seleccionar ...') {
                return redirect()->route('empleados.create')->with('error', 'Departamento no seleccionado');         
            }             
    
            // Verificar si se seleccionó "Seleccionar..." en el campo "municipio"
            if ($request->input('municipio') === 'Seleccionar ...') {
                return redirect()->route('empleados.create')->with('error', 'Municipio no seleccionado');        
            }    
    
    
            $empleado = new Usuario();
    
            $empleado->dui = $request->input('dui_opcion');
            $empleado->nombres = $request->input('nombre_opcion');
            $empleado->apellidos = $request->input('apellido_opcion');
            $empleado->telefono = $request->input('telefono_opcion');
            $empleado->departamento = $request->input('departamento');
            $empleado->municipio = $request->input('municipio');
            $empleado->direccion = $request->input('direccion_opcion');
            $empleado->email = $request->input('email_opcion');
    
            $empleado->creado_por = Auth::user()->nombres;
            $empleado->fecha_creacion = now();
    
            $empleado->save();
    
    
             //Obtener ID del usuario que se esta ingresando
            $usuarioId = $empleado->usuario_id;
    
            // Busco el ID del rol "cliente" en la tabla roles
            $rolEmpleado = Role::where('role', 'Empleado')->first();
    
            if (!$rolEmpleado) {
                return redirect()->route('empleados.index')->with('error', 'El rol "Empleado" no se encontró.');
            }
    
    
            $detalleRol = new DetalleRole();
            $detalleRol->role_id = $rolEmpleado->role_id;
            $detalleRol->usuario_id = $usuarioId;
            $detalleRol->save();
    
    
    
            return redirect()->route('empleados.index')->with('success', 'El registro se ha agregado con éxito.');
    
        } catch (\Throwable $th) {
            return redirect()->route('empleados.index')->with('error', 'Sucedio un error al ingresar el empleado');
        }
    }


    public function edit($id)
    {
        try {

            $empleados = Usuario::find($id);
    
            // Verifica si el registro existe
            if (!$empleados) {
                return redirect()->back()->with('error', 'Ha ocurrido un error. No se pudo realizar la operación.');
            }
    
            return view('empleados.edit', compact('empleados'));
    
    
            } catch (\Exception $e) {
                Log::error($e->getMessage());
                return redirect()->route('empleados.index')->with('error', 'Error al cargar la página para editar el cliente');
            }
    }


    public function update(Request $request, $id)
    {
        try{

            $empleado = Usuario::find($id);
    
            if (!$empleado) {
                return redirect()->back()->with('error', 'Ha ocurrido un error. No se pudo realizar la operación.');
            }
    
            //validar teléfono
            $existingPhone = Usuario::where('telefono', $request->input('telefono_opcion'))
                ->where('usuario_id', '<>', $id)
                ->first();     
     
             if ($existingPhone) {
                return redirect()->route('empleados.index')->with('error', 'El teléfono ya está registrado en la base de datos.');         
            }
    
            //validar email
            $existingEmail = Usuario::where('email', $request->input('email_opcion'))
                ->where('usuario_id', '<>', $id)
                ->first();
    
            if ($existingEmail) {
                return redirect()->route('empleados.index')->with('error', 'El correo electrónico ya está registrado en la base de datos.');         
            }
    
              // Definimos las reglas de validación
              $rules = [
    
                'nombre_opcion' => 'required',
                'apellido_opcion' => 'required',
    
                'telefono_opcion' => 'required|regex:/^\d{4}-\d{4}$/|unique:usuarios,telefono,'.$id.',usuario_id',
    
                'direccion_opcion' => 'nullable',
    
                'email_opcion' => 'required|email|unique:usuarios,email,'.$id.',usuario_id',
            ];
    
            $messages = [
    
                'nombre_opcion.required' => 'Debes registrar al menos un nombre',
                'apellido_opcion.required' => 'Debes registrar al menos un apellido',
    
    
                'telefono_opcion.required' => 'El campo "Teléfono" es obligatorio.',
                'telefono_opcion.unique' => 'Este teléfono ya está registrado, intentelo de nuevo.',
                'telefono_opcion.regex' => 'El campo "Teléfono" debe tener el formato correcto (por ejemplo, 7889-1256).',
    
                'email_opcion.unique' => 'El email ya está registrado, intentelo de nuevo',
                'email_opcion.required' => 'El email es requerido',
                'email_opcion.email' => 'El campo "Email" debe ser una dirección de correo electrónico válida.',
    
            ];
    
            $validator = Validator::make($request->all(), $rules, $messages);
    
            if ($validator->fails()) {
                return redirect()
                    ->route('empleados.update') 
                    ->withErrors($validator)
                    ->withInput();
            }
    
         
    
    
            $empleado->dui = $request->input('dui_opcion');
            $empleado->nombres = $request->input('nombre_opcion');
            $empleado->apellidos = $request->input('apellido_opcion');
            $empleado->telefono = $request->input('telefono_opcion');
    
            $departamentoSeleccionado = $request->input('departamento');
            $municipioSeleccionado = $request->input('municipio');
    
            // Verificar si se seleccionó "Seleccionar..." en el campo "departamento"
    if ($departamentoSeleccionado === 'Seleccionar ...') {
        return redirect()->route('empleados.index')->with('error', 'Debes seleccionar un departamento válido.');         
    
    }
    
    // Verificar si se seleccionó "Seleccionar..." en el campo "municipio"
    if ($municipioSeleccionado === 'Seleccionar ...') {
        return redirect()->route('empleados.index')->with('error', 'Debes seleccionar un municipio válido.');         
    
    }
    
            $empleado->departamento = $departamentoSeleccionado;
            $empleado->municipio = $municipioSeleccionado;
            $empleado->direccion = $request->input('direccion_opcion');
            $empleado->email = $request->input('email_opcion');
            
            $empleado->save();
    
            return redirect()->route('empleados.index')->with('success', 'Cliente actualizado exitosamente');
            
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Throwable $th) {
            return redirect()->route('empleados.index')->with('error', 'Sucedio un error al actualizar el cliente, revisa que todos los campos sean correctos');
        }
    }

    public function destroy($id)
    {
        try {

            $action = request()->input('action');
    
            if ($action === 'update') {
    
                $empleado = Usuario::find($id);
    
                if (!$empleado) {
                    return redirect()->route('empleados.index')->with('error', 'Ha ocurrido un error. No se pudo realizar la operación.');
                }
    
                $empleado->bloqueado_por = Auth::user()->nombres;
                $empleado->fecha_bloqueo = now();
    
                $empleado->save();
    
            return redirect()->route('empleados.index')->with('success', 'El registro se ha bloqueado con éxito.');
            }
    
        } catch (QueryException $e) {
            // Manejo de excepciones SQL
            Log::error($e->getMessage());
            return redirect()->route('empleados.index')->with('error', 'Error de base de datos al eliminar el cliente');
        } catch (\Exception $e) {
            // Manejo de otras excepciones
            Log::error($e->getMessage());
            return redirect()->route('empleados.index')->with('error', 'Error al eliminar el cliente');
        }
        
    }

    public function unblock($id)
    {
        try {
    
            $empleado = Usuario::find($id);
    
            // Verificar si el cliente está bloqueado
            if (!$empleado->bloqueado_por) {
                return redirect()->route('empleados.index')->with('error', 'El cliente no está bloqueado.');
            }
    
            // Desbloquear al cliente
            $empleado->bloqueado_por = null;
            $empleado->fecha_actualizacion = now();
            $empleado->actualizado_por = Auth::user()->nombres;
            $empleado->fecha_bloqueo = null;
    
    
            //Guardo los cambios
            $empleado->save();
    
            return redirect()->route('empleados.index')->with('success', 'El cliente ha sido desbloqueado con éxito.');
    
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return redirect()->route('empleados.index')->with('error', 'Error al desbloquear el cliente.');
        }
    }



}
