<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\UserFile;
use Illuminate\Http\Request;
use App\Http\Requests;
use Storage;
use File;
use Illuminate\Support\Facades\Validator;
use Log;
use Carbon\Carbon;

class MediaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $user = $request->user();
        if(!is_null($user) && $user->exists){
            $files = $request->file('files');
            if(is_null($files)){
                return response()->json(['success'=> false, 'error'=> ['common' => trans('validation.exists_file')]], 400);
            }
            if(!is_array($files)) {         $files = [$files];      }
            if($request->input('type') == 'image'){
                $validator = Validator::make(
                    $request->all(),
                    [
                        'files.*' => 'image'
                    ]
                );
                if ($validator->fails())
                {
                    return response()->json(
                        [
                            'success' => false, 'error'=> ['message' => $validator->messages()],
                        ],406);
                }
            }
            foreach ($files as $file){
                if ($file->isValid()) {
                    $hash = hash_file('sha256', $file->getPathname());
                    $size = $file->getSize();
                    $timestamp = time();
                    //$filename = $hash.'.'.$file->getClientOriginalExtension();
                    $filename = $hash.'.'.$file->getClientOriginalExtension();

                    //Сохраняем паку относительно серверного времени
                    $time = Carbon::createFromTimestamp($timestamp, 'UTC');
                    $filepath = $time->toDateString().DIRECTORY_SEPARATOR.$filename;
                    if(!Storage::disk('public')->exists($filepath))
                        Storage::disk('public')->put($filepath, File::get($file));
                    //$file->move('../storage/app', $hash);
                    switch ($request->input('type')){
                        case 'image': $type = UserFile::TYPE_IMAGE;
                            break;
                        default: $type = UserFile::TYPE_OTHER;
                    }
                    //То что созхраняем в базу
                    $file_model = $user->files()->create([
                        'name' => $file->getClientOriginalName(),
                        'hash' => $hash,
                        'size' => $size,
                        'user_id' => $user->user_id,
                        'created_at' => $timestamp,
                        'type' => $type,
                        'mime' => $file->getClientMimeType(),
                    ]);
                    //Возвращаемые данные
                    $r_files[] = [
                        'id' => $file_model->id,
                        'url' => Storage::url($filepath),
                        'name' => $file_model->name,
                        'size' => $file_model->size,
                        'created_at' => $file_model->created_at->format('Y-m-d H:i:sO'),
                        'type' => $file_model->type,
                        'mime' => $file_model->mime,
                    ];
                    //Отправляем файлы в обработку
//                    if($request->input('type') == 'image')
//                    {
//                        $this->dispatch((new ImageProcessing($file_model, ['filepath' => $filepath, 'savedName' => $filename, 'date' => $time->toDateString()]))->onQueue(config('cache.prefix').'_images'));
//                    }

                }
            }
            return response()->json(['success'=> true, 'files' => $r_files], 201);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
