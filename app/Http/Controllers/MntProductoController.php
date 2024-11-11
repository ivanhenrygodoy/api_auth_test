<?php

namespace App\Http\Controllers;

use App\Http\Requests\DocumentProductRequest;
use App\Http\Requests\ProductPostRequest;
use App\Http\Requests\ProductPutRequest;
use App\Http\Responses\ApiResponse;
use App\Models\MntDocumentoCertificacion;
use App\Models\MntProducto;
use App\Traits\Documentos;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;



class MntProductoController extends Controller
{
    use Documentos;
    public function agregar_producto(ProductPostRequest $request){
        DB::beginTransaction();
        try {
    
            $nuevoProducto = new MntProducto();
            $nuevoProducto->id_establecimiento_origen = $request->id_establecimiento_origen;
            $nuevoProducto->id_categoria_producto = $request->id_categoria_producto;
            $nuevoProducto->nombre = $request->nombre;
            $nuevoProducto->codigo = $request->codigo;
            $nuevoProducto->save();

            if ($request->hasFile('documentos')) {
                try {

                    $request->validate([
                        'nombre_documento' => 'required|array',
                        'nombre_documento.*' => 'required|string',
                        'documentos' => 'required|array',
                        'documentos.*' => 'file|mimes:pdf,doc,docx',
                    ]);

                    $documentos = $request->file('documentos');
                    $nombresDocumentos = $request->input('nombre_documento');
                    $this->ArrayDocumentos($nuevoProducto->id, $documentos, $nombresDocumentos);
                } catch (\Exception $e) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 422,
                        'errors' => ['message' => ['Error al guardar los documentos']],
                        'message' => $e->getMessage()
                    ], 422);
                }
            }
            DB::commit();

            return Response()->json([
                'status' => Response::HTTP_OK,
                'data' => ['message' => ['El producto se ah agregado correctamente']],
                'errors' => []
            ], Response::HTTP_OK);

        } catch (Exception $e) {
            DB::rollBack();

            return Response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'data' => [],
                'errors' => [
                    'message' => $e->getMessage()
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function detalle_producto($idProducto){

        try {
            $existProduct = MntProducto::with(['establecimientoOrigen', 'categoriaProducto'])
            ->where('id', $idProducto)->first();
    
            if (!$existProduct) {
                return Response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'data' => [],
                    'errors' => [
                        'message' => ['No se encontró el product.']
                    ]
                ], Response::HTTP_NOT_FOUND);
            }
    

            $detalle_producto = [
                'datos_producto' => [
                    'id_producto' => $existProduct->id ?? '',
                    'nombre_producto' => $existProduct->nombre ?? '',
                    'codigo_producto' => $existProduct->codigo ?? '',
                    'establecimiento_origen' => $existProduct->establecimientoOrigen ?? '',
                    'categoria_origen' => $existProduct->categoriaProducto ?? '',
                ],

            ];

            return Response()->json([
                'status' => Response::HTTP_OK,
                'data' =>$detalle_producto,
                'errors' => []
            ], Response::HTTP_OK);


        } catch (Exception $e) {
            DB::rollBack();

            return Response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'data' => [],
                'errors' => [
                    'message' => $e->getMessage()
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }



    }

    public function ArrayDocumentos($idProducto, $documentos, $nombresDocumentos)
    {
        try {
            $path = storage_path('app/documento-regulacion-producto/documentos');
            $this->existsPathDocument($path);
    
            $counter = 0;
            foreach ($documentos as $index => $documento) {
                $random = substr(str_shuffle("0123456789"), 0, 10);
                $formatoDoc = $documento->getClientOriginalExtension();
                $nombreDoc = $random . '_' . Str::slug(Carbon::now()->format('Y_m_d H_i_s'), '_') . '.' . $formatoDoc;
    
                Storage::disk('documento-regulacion-producto')->put($nombreDoc, file_get_contents($documento));
    
                $fileName = $nombresDocumentos[$index];
    
                $existingDocumento = MntDocumentoCertificacion::where('nombre_archivo', $fileName)->first();
    
                if ($existingDocumento) {
                    throw new \Exception('Ya existe un archivo con el nombre: ' . $fileName );
                }
    
                $nuevoDocumento = new MntDocumentoCertificacion();
                $nuevoDocumento->nombre_archivo = $fileName;
                $nuevoDocumento->id_producto = $idProducto;
                $nuevoDocumento->ruta_documento_certificacion = 'storage/app/documento-regulacion-producto/documentos/' . $nombreDoc;
                $nuevoDocumento->save();
    
                if (!$nuevoDocumento) {
                    return ApiResponse::error('No se pudo guardar el documento', 500);
                }
    
                $counter++;
            }
        } catch (Exception $e) {
            DB::rollBack();

            Log::error("Error al procesar los documentos de la solicitud: " . $e->getMessage());
            Log::error("Documento id: " . $nombreDoc);
            throw $e;
            return ApiResponse::error('Error al procesar los documentos de la solicitud', 500);
        }
    }
    
    public function index_producto(Request $request) {
        $filtro = $request->filtro;
        $paginate = $request->paginate ? $request->paginate : 10;
        $listProduct = MntProducto::with(['establecimientoOrigen', 'categoriaProducto'])
            ->orderBy('created_at', 'desc');
    
        if (!empty($filtro)) {
            $listProduct->where(function($query) use ($filtro) {
                $query->where('nombre', 'ilike', '%' . $filtro . '%')
                    ->orWhereHas('establecimientoOrigen', function ($query) use ($filtro) {
                        $query->where('nombre', 'ilike', '%' . $filtro . '%');
                    })
                    ->orWhereHas('categoriaProducto', function ($query) use ($filtro) {
                        $query->where('nombre', 'ilike', '%' . $filtro . '%');
                    })
                    ->orWhere('codigo', 'ilike', '%' . $filtro . '%');
            });
        }
    
        $listProduct = $listProduct->paginate($paginate);
    
        $listProduct->getCollection()->transform(function ($item) {
            return [
                'id' => $item->id,
                'nombre' => $item->nombre,
                'codigo' => $item->codigo,
                'establecimiento_origen' => $item->establecimientoOrigen->nombre,
                'categoria_producto' => $item->categoriaProducto->nombre,
                'activo' => $item->activo,
            ];
        });
    
        return response($listProduct, Response::HTTP_OK);
    }

    public function actualizar_producto(Request $request, $idProducto)
    {
        try {
            $updateProducto = MntProducto::where('id', $idProducto)->first();

            if (!$updateProducto) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'data' => [],
                    'errors' => ['message' => 'Producto no encontrado']
                ], Response::HTTP_NOT_FOUND);
            }

            $updateProducto->id_establecimiento_origen = $request->id_establecimiento_origen;
            $updateProducto->id_categoria_producto = $request->id_categoria_producto;
            $updateProducto->nombre = $request->nombre;
            $updateProducto->codigo = $request->codigo;
            $updateProducto->save();
    
            DB::commit();
    
            return response()->json([
                'status' => Response::HTTP_OK,
                'data' => ['message' => 'El producto se ha actualizado correctamente'],
                'errors' => []
            ], Response::HTTP_OK);
    
        } catch (Exception $e) {
            DB::rollBack();
    
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'data' => [],
                'errors' => ['message' => $e->getMessage()]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function cambio_estado_producto($idProduct) 
    {
        DB::beginTransaction();
        try {
            $busqProducto = MntProducto::where('id', $idProduct)->first();
            if (!$busqProducto) {
                return response()->json(
                    [
                        'status' => '404',
                        'data' => [],
                        'errors' => [
                            'message' => 'Ocurrio un problema, no se encuentra el registro.'
                        ]
                    ],
                    404
                );
            }

            $busqProducto->activo = !$busqProducto->activo;
            $busqProducto->save();

            $message = $busqProducto->activo ? 'activó' : 'desactivó';
            DB::commit();

            return response()->json([
                'status' => '200',
                'data' => [
                    'message' => ["Se $message correctamente."]
                ],
                'errors' => []
            ], 200);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'data' => [],
                'errors' => [
                    'message' => $e->getMessage()
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function agregar_archivo(DocumentProductRequest $request) 
    {

        $existingDocumento = MntDocumentoCertificacion::where('nombre_archivo', $request->nombre_archivo)
        ->where('id_producto', $request->id_producto)
        ->first();

        if ($existingDocumento) {
            return Response()->json([
                'status' => Response::HTTP_BAD_REQUEST,
                'data' => [],
                'errors' => [
                    'message' => ['Ya existe un archivo con el mismo nombre.']
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

        $path = storage_path('app/documento-regulacion-producto/documentos');
        $this->existsPathDocument($path);
        $random = substr(str_shuffle("0123456789"), 0, 10);
        $formatoDoc = $request->documento->getClientOriginalExtension();
        $nombreDoc = $random . '_' . Str::slug(Carbon::now()->format('Y_m_d H_i_s'), '_') . '.' . $formatoDoc;

        Storage::disk('documento-regulacion-producto')->put($nombreDoc, file_get_contents($request->documento));

        if (!Storage::disk('documento-regulacion-producto')->exists($nombreDoc)) {
            return ApiResponse::error('Error al guardar el documento', 500);
        }

        $nuevoDocumento = new MntDocumentoCertificacion();
        $nuevoDocumento->id_producto = $request->id_producto;
        $nuevoDocumento->nombre_archivo = $request->nombre_archivo;
        $nuevoDocumento->ruta_documento_certificacion = 'storage/app/documento-regulacion-producto/documentos/' . $nombreDoc;
        $nuevoDocumento->save();

        
        DB::commit();
        if (!$nuevoDocumento) {
            return ApiResponse::error('No se pudo guardar el documento', 500);
        }

        return Response()->json([
            'status' => Response::HTTP_OK,
            'data' => ['message' => ['El archivo se ah agregado correctamente']],
            'errors' => []
        ], Response::HTTP_OK);


    }

    public function detalle_archivo($idDocumentoDespido)
    {
        try {

            $documentoExist = MntDocumentoCertificacion::where('id',$idDocumentoDespido)
            ->first();

            if (!$documentoExist) {
                return Response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'data' => [],
                    'errors' => [
                        'message' => ['No se encontró el recurso']
                    ]
                ], Response::HTTP_NOT_FOUND);
            }

            $nombreArchivo = basename($documentoExist->ruta_documento_certificacion);

            if (!Storage::disk('documento-regulacion-producto')->exists($nombreArchivo)) {
                return ApiResponse::error('El documento no se encuentra disponible', 404, $documentoExist->ruta_archivo);
            }

            $rutaArchivo = storage_path('/app/documento-regulacion-producto/documentos/' . $nombreArchivo);

            // Lee el contenido del archivo y lo codifica en base64
            $contenidoBase64 = base64_encode(file_get_contents($rutaArchivo));

            return [
                'documento' => [
                    'id' => $documentoExist->id,
                    'nombre_archivo' => $documentoExist->nombre_archivo,
                    'ruta_documento_certificacion' =>$documentoExist->ruta_documento_certificacion,
                    'id_producto' => $documentoExist->id_producto,
                    'activo' => $documentoExist->activo,
                    'archivo' => $contenidoBase64
                ]
            ];
            return ApiResponse::success(['documento_regulatorio' => $contenidoBase64], 200);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al recuperar el documento', 500, $e->getMessage());
        }
    }

    public function listado_archivos(Request $request)
    {
        try {
            $paginate = $request->paginate ? $request->paginate : 10;

            $documentosProducto = MntDocumentoCertificacion::where('id_producto', $request->id_producto)
            ->paginate($paginate);

            if (!$documentosProducto) {
                return ApiResponse::error('Los documentos no existen para el producto seleccionado', 404);
            }

            return Response()->json([
                'status' => Response::HTTP_OK,
                'data' =>$documentosProducto,
                'errors' => []
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            DB::rollBack();

            return Response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'data' => [],
                'errors' => [
                    'message' => $e->getMessage()
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function eliminar_archivos($idDocumento)
    {
        DB::beginTransaction();
        try {
            $documentoExist = MntDocumentoCertificacion::where('id',$idDocumento)
            ->first();

            if (!$documentoExist) {
                return Response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'data' => [],
                    'errors' => [
                        'message' => ['No se encontró el recurso']
                    ]
                ], Response::HTTP_NOT_FOUND);
            }

            $nombreArchivo = basename($documentoExist->ruta_documento_certificacion);
            if (!$documentoExist) {
                return ApiResponse::error('El documento especificado no existe', 404);
            }

            $rutaArchivo = $documentoExist->ruta_documento_certificacion;

            if (Storage::disk('documento-regulacion-producto')->exists($nombreArchivo)) {
                Storage::disk('documento-regulacion-producto')->delete($nombreArchivo);
            }

            $documentoExist->delete();
            DB::commit();
        return Response()->json([
            'status' => Response::HTTP_OK,
            'data' => ['message' => 'El archivo se ah eliminado correctamente'],
            'errors' => []
        ], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollback();
            return ApiResponse::error('Error al eliminar el documento', 500);
        }
    }

    public function delete_producto($idProducto) 
    {
        DB::beginTransaction();
        try {
            $busqProducto = MntProducto::where('id', $idProducto)->first();
            if(!$busqProducto) {
                return response()->json(
                    [
                        'status' => '404',
                        'data' => [],
                        'errors' => [
                            'message' => 'Ocurrio un problema, no se encuentra el registro.'
                        ]
                    ],
                    404
                );
            }

            $busqProducto->delete();

            DB::commit();

            return response()->json([
                'status' => Response::HTTP_OK,
                'data' => ['message' => 'El producto se ha eliminado correctamente'],
                'errors' => []
            ], Response::HTTP_OK);

        } catch (Exception $e) {
            DB::rollBack();
    
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'data' => [],
                'errors' => ['message' => $e->getMessage()]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }
    

}
