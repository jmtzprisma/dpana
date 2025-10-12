<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\Api\UserResource;
use App\Models\MediaManager;
use App\Mail\EmailManager;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Laravel\Sanctum\PersonalAccessToken;

use SapientPro\ImageComparator\ImageComparator;

use Twilio\Rest\Client;
use Str;
use Mail;

use GoogleCloudVision\GoogleCloudVision;
use GoogleCloudVision\Request\AnnotateImageRequest;

use Aws\S3\S3Client;


class AuthController extends Controller
{

    # make new registration here
    protected function create(array $data)
    {
        if (filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $user = User::create([
                'name' => $data['name'] . ' ' . $data['lastname'],
                'email' => $data['email'],
                'cedula' => $data['cedula'],
                'birthday' => $data['birthday'],
                'phone' => validatePhone($data['phone']),
                'password' => Hash::make($data['password']),
            ]);
            // set guest_user_id to user_id from carts
            return $user;
        }
        return null;
    }
    
    public function validaImage(Request $request)
    {
        ini_set('max_execution_time', 300);

        if ($request->hasFile('imageDni')) {
           $image = base64_encode(file_get_contents($request->file('imageDni')));

            //prepare request
            $request = new AnnotateImageRequest();
            $request->setImage($image);
            $request->setFeature("TEXT_DETECTION");
            $gcvRequest = new GoogleCloudVision([$request],  env('GOOGLE_CLOUD_KEY'));
            //send annotation request
            $response = $gcvRequest->annotate();
            dd($response);
        }
    }

    # register new customer here
    public function register(Request $request)
    {
        ini_set('max_execution_time', 300);

        if (filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            if (User::where('email', $request->email)->first() != null) {
                return $this->registrationFailed(localize('Ya existe una cuenta creada con este dispositivo. No se puede crear más de 1 cuenta por dispositivo'));
            }
        }

        if ($request->phone != null) {
            if (User::where('phone', $request->phone)->first() != null) {
                return $this->registrationFailed(localize('Ya existe un usuario con este número de teléfono.'));
            }
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->registrationFailed($validator->errors()->all());
        }

        $user = $this->create($request->all());
        # verification
        if ($user) {
            
            if ($request->hasFile('documentImage')) {
                $mediaFile = new MediaManager();
                $mediaFile->user_id = $user->id;
                $mediaFile->media_file = $request->file('documentImage')->store('uploads/media');
                $mediaFile->media_size = $request->file('documentImage')->getSize();
                $mediaFile->media_name = $request->file('documentImage')->getClientOriginalName();
                $mediaFile->media_extension = $request->file('documentImage')->getClientOriginalExtension();
                $mediaFile->kyc = true;

                if (getFileType(Str::lower($mediaFile->media_extension)) != null) {
                    $mediaFile->media_type = getFileType(Str::lower($mediaFile->media_extension));
                } else {
                    $mediaFile->media_type = "unknown";
                }
                $mediaFile->save();
                //$user->avatar = $mediaFile->id;
            }
            if ($request->hasFile('fullImage')) {
                $mediaFile = new MediaManager();
                $mediaFile->user_id = $user->id;
                $mediaFile->media_file = $request->file('fullImage')->store('uploads/media');
                $mediaFile->media_size = $request->file('fullImage')->getSize();
                $mediaFile->media_name = $request->file('fullImage')->getClientOriginalName();
                $mediaFile->media_extension = $request->file('fullImage')->getClientOriginalExtension();
                $mediaFile->kyc = true;

                if (getFileType(Str::lower($mediaFile->media_extension)) != null) {
                    $mediaFile->media_type = getFileType(Str::lower($mediaFile->media_extension));
                } else {
                    $mediaFile->media_type = "unknown";
                }
                $mediaFile->save();
                //$user->avatar = $mediaFile->id;
            }
            if ($request->hasFile('fileDni')) {
                $mediaFile = new MediaManager();
                $mediaFile->user_id = $user->id;
                $mediaFile->media_file = $request->file('fileDni')->store('uploads/media');
                $path_dni = $request->file('fileDni')->store('uploads/media');
                $mediaFile->media_size = $request->file('fileDni')->getSize();
                $mediaFile->media_name = $request->file('fileDni')->getClientOriginalName();
                $mediaFile->media_extension = $request->file('fileDni')->getClientOriginalExtension();
                $mediaFile->kyc = true;

                if (getFileType(Str::lower($mediaFile->media_extension)) != null) {
                    $mediaFile->media_type = getFileType(Str::lower($mediaFile->media_extension));
                } else {
                    $mediaFile->media_type = "unknown";
                }
                $mediaFile->save();
                //$user->avatar = $mediaFile->id;
            }

            if ($request->hasFile('fileEyes')) {
                $mediaFile = new MediaManager();
                $mediaFile->user_id = $user->id;
                $mediaFile->media_file = $request->file('fileEyes')->store('uploads/media');
                $path_eyes = $request->file('fileDni')->store('uploads/media');
                $mediaFile->media_size = $request->file('fileEyes')->getSize();
                $mediaFile->media_name = $request->file('fileEyes')->getClientOriginalName();
                $mediaFile->media_extension = $request->file('fileEyes')->getClientOriginalExtension();
                $mediaFile->kyc = true;

                if (getFileType(Str::lower($mediaFile->media_extension)) != null) {
                    $mediaFile->media_type = getFileType(Str::lower($mediaFile->media_extension));
                } else {
                    $mediaFile->media_type = "unknown";
                }
                $mediaFile->save();
                $user->avatar = $mediaFile->id;
            }
            
            if ($request->hasFile('fileRight')) {
                $mediaFile = new MediaManager();
                $mediaFile->user_id = $user->id;
                $mediaFile->media_file = $request->file('fileRight')->store('uploads/media');
                $mediaFile->media_size = $request->file('fileRight')->getSize();
                $mediaFile->media_name = $request->file('fileRight')->getClientOriginalName();
                $mediaFile->media_extension = $request->file('fileRight')->getClientOriginalExtension();
                $mediaFile->kyc = true;

                if (getFileType(Str::lower($mediaFile->media_extension)) != null) {
                    $mediaFile->media_type = getFileType(Str::lower($mediaFile->media_extension));
                } else {
                    $mediaFile->media_type = "unknown";
                }
                $mediaFile->save();
                // $user->avatar = $mediaFile->id;
            }
            
            if ($request->hasFile('fileLeft')) {
                $mediaFile = new MediaManager();
                $mediaFile->user_id = $user->id;
                $mediaFile->media_file = $request->file('fileLeft')->store('uploads/media');
                $mediaFile->media_size = $request->file('fileLeft')->getSize();
                $mediaFile->media_name = $request->file('fileLeft')->getClientOriginalName();
                $mediaFile->media_extension = $request->file('fileLeft')->getClientOriginalExtension();
                $mediaFile->kyc = true;

                if (getFileType(Str::lower($mediaFile->media_extension)) != null) {
                    $mediaFile->media_type = getFileType(Str::lower($mediaFile->media_extension));
                } else {
                    $mediaFile->media_type = "unknown";
                }
                $mediaFile->save();
                // $user->avatar = $mediaFile->id;
            }
            
            if ($request->hasFile('fileDni')) { //&& $request->hasFile('fileEyes')
            
                // $image1 = public_path($path_eyes);
                // $image2 = public_path($path_dni);
                
                // $imageComparator = new ImageComparator();
                
                // $similarity = $imageComparator->compare($image1, $image2);
                
                // if($similarity > 50) // 92.2
                // {
                    $limit_credit = 10000;
                    
                    if(getSetting('credit_limits') != null)
                    {
                        $credit_limits = json_decode(getSetting('credit_limits'));
                        foreach($credit_limits as $itm)
                        {
                            $age = Carbon::parse($request->birthday)->age;
                            if($age > $itm->from && $age < $itm->to){
                                $limit_credit = $itm->limit;
                            }
                        }
                    }

                    $user->kyc = 1;
                    $user->limit_credit = $limit_credit;
                    $user->limit_credit_general = $limit_credit;
                    $user->save();
                // }else{
                //     $user->kyc = 2;
                //     $user->save();
                // }
            }
            

            
            if (getSetting('registration_verification_with') == "disable") {
                $user->email_or_otp_verified = 1;
                $user->email_verified_at = Carbon::now();
                $user->save();
                return $this->loginSuccess($user);
            } else {
                if (getSetting('registration_verification_with') == 'email') {
                    try {
                        $user->sendVerificationNotification();
                        return $this->loginSuccess($user);
                        // return response()->json([
                        //     'result' => true,
                        //     'message' => localize('Registration successful. Please verify your email.'),
                        //     'access_token' => '',
                        //     'token_type' => '',
                        //     "user"=>[
                        //         'name' => '',
                        //         'email' => '',
                        //         'phone' => '',
                        //         'balance' => '',
                        //         'avatar' => ''
                        //     ]
                        // ]);
                    } catch (\Throwable $th) {
                        $user->delete();
                        return $this->registrationFailed(localize('Error en el registro. Inténtalo de nuevo más tarde.'));
                    }
                }
                // else being handled in verification controller
            }
        } else {
            return $this->registrationFailed("Registro fallido");
        }
    }


    public function login(Request $request)
    {
        $user = User::where('user_type', $request->type)
            ->where('email', $request->email)
            ->orWhere('phone', $request->email)
            ->first();
        if ($user != null) {
            if (!$user->is_banned) {
                if (Hash::check($request->password, $user->password)) {

                    if ($user->email_verified_at == null) {
                        return $this->loginFailed(localize('Por favor verifica tu cuenta'));
                    }
                    return $this->loginSuccess($user);
                } else {
                    return $this->loginFailed(localize('Las credenciales son incorrectas.'));
                }
            } else {
                return $this->loginFailed(localize('El usuario esta prohibido'));
            }
        } else {
            return $this->loginFailed(localize('Usuario no encontrado'));
        }
    }

    protected function loginSuccess($user)
    {
        $token = $user->createToken('API Token')->plainTextToken;
        return response()->json([
            'result' => true,
            'message' => localize('Se ha iniciado sesión correctamente'),
            'access_token' => $token,
            'token_type' => 'Bearer',
            "user"=>[
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'balance' => $user->user_balance,
                'avatar' => uploadedAsset($user->avatar)
            ]
        ]);
    }

    protected function loginFailed($message)
    {
        return response()->json([
            'result' => false,
            'message' => $message,
            'access_token' => '',
            'token_type' => '',
            "user"=>   [
                'name' => "",
                'email' => "",
                'phone' => "",
                'balance' => "",
                'avatar' => ""
                ]
        ]);
    }

    protected function registrationFailed($message)
    {
        return response()->json([
            'result' => false,
            'message' => $message,
            'access_token' => '',
            'token_type' => '',
            "user"=>[
                'name' => '',
                'email' => '',
                'phone' => '',
                'balance' => '',
                'avatar' => ''
            ]
        ]);
    }

    public function checkToken(Request $request)
    {

        $false_response = [
            'result' => false,
             "user"=>   [
                'name' => "",
                'email' => "",
                'phone' => "",
                'balance' => "",
                'avatar' => ""
                ]
        ];

        $token=PersonalAccessToken::findToken($request->bearerToken());
        if (!$token) {
            return response()->json($false_response);
        }

        $user = $token->tokenable;

        if ($user->is_banned) {
        return response()->json([
            'result' => false,
            "is_banned"=>true,
            'message' => localize("You have been banned")
        ]);
        }

        if ($user == null) {
            return response()->json($false_response);

        }

        return response()->json([
            'result' => true,
            "user"=>[
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'balance' => $user->user_balance,
                'avatar' => uploadedAsset($user->avatar)
            ]
        ]);

    }

    public function logout(Request $request)
    {

        $false_response = [
            'result' => false,
             "user"=>   [
                'name' => "",
                'email' => "",
                'phone' => "",
                'balance' => "",
                'avatar' => ""
                ]
        ];

        $user = auth()->user();
        $user->tokens()->where('id', $user->currentAccessToken()->id)->delete();

        if ($user->is_banned) {
        return response()->json([
            'result' => false,
            "is_banned"=>true,
            'message' => localize("You have been banned")
        ]);
        }

        if ($user == null) {
            return response()->json($false_response);

        }

        return response()->json([
            'result' => true,
            "user"=>[
                'name' =>"",
                'email' => "",
                'phone' => "",
                'balance' => "",
                'avatar' => ""
            ]
        ]);

    }

    public function sendCodeVerifyEmail(Request $request)
    {
        
        if (filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            if (User::where('email', $request->email)->first() != null) {

                return $this->registrationFailed(localize('El correo electrónico o teléfono ya existe.'));
            }
        }
        
        $array['view'] = 'emails.bulkEmail';
        $array['subject'] = "Código de verificación";
        $array['from'] = env('MAIL_FROM_ADDRESS');
        $array['content'] = "Su código de verificación es " . $request->code;
        try {
            Mail::to($request->email)->queue(new EmailManager($array));
            
            return response()->json([
                'result' => true,
                'message' => localize('Código enviado.')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => localize('Verifique su email.') . $e->getMessage()
            ]);
        }

    }

    public function sendCodeRecoveryEmail(Request $request)
    {
        
        if (filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            if (!($user = User::where('email', $request->email)->first())) {

                return $this->registrationFailed(localize('No existe una cuenta con el correo electrónico proporcionado'));
            }
        }else{
            return $this->registrationFailed(localize('Correo electrónico invalido'));
        }
        
        $user->verification_code = $request->code;
        $user->save();

        $array['view'] = 'emails.bulkEmail';
        $array['subject'] = "Código de verificación";
        $array['from'] = env('MAIL_FROM_ADDRESS');
        $array['content'] = "Su código de verificación es " . $request->code;
        try {
            Mail::to($request->email)->queue(new EmailManager($array));
            
            return response()->json([
                'result' => true,
                'message' => localize('Código enviado.')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => localize('Verifique su email.')
            ]);
        }

    }

    public function sendCodeVerifyMobile(Request $request)
    {
        if (User::where('phone', $request->mobile)->first() != null) {
            return $this->registrationFailed(localize('Ya existe un usuario con este número de teléfono.'));
        }
    
        $sid = getenv("TWILIO_SID");
        $token = getenv("TWILIO_AUTH_TOKEN");
        $twilio = new Client($sid, $token);

        $code = $request->code;
        $phone = $request->country_code . ($request->country_code == '52' || $request->country_code == '+52' ? '1' : '') . $request->mobile;
        $phone = $this->cleanMobile($phone);
        try {
            $message = $twilio->messages
            //->create("+5219516152009", // to
            ->create($phone, // to
                array(
                    "from" => "+13344497503",
                    "body" => "Tu código de verificación es " . $code
                )
            );
            return response()->json([
                'result' => true,
                'message' => localize('Código enviado.')
            ]);
        } catch(\Exception $e)
        {
            return response()->json([
                'result' => false,
                'message' => localize('Verifique su número de telefono.')
            ]);
        }
    }
    
    private function cleanMobile($strMobile){
        $mobile = null;

        $strMobile = str_replace(' ','',$strMobile);
        $strMobile = str_replace('-','',$strMobile);

        if(substr($strMobile,0,1) == '+'){
            $mobile = $strMobile;
        }else{
            $mobile = '+' . $strMobile;
        }

        return $mobile;
    }

    
    # updatePw
    public function updatePw(Request $request)
    {
        $user = User::where('verification_code', $request->verification_code)->first();
        if (is_null($user)) {
            return $this->registrationFailed(localize('Código invalido'));
        }

        $request->validate(
            [
                'password' => 'required|confirmed|min:6'
            ]
        );

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'result' => true,
            'message' => localize('Contraseña actualizada correctamente.')
        ]);
    }

    public function verifyImageIsDni(Request $request)
    {
        $path_dni = null;
        if ($request->hasFile('fileDni')) {
            $path_dni = $request->file('fileDni')->store('uploads/media');
        }else{
            return response()->json([
                'result' => false
            ]);
        }

        $client = new \Aws\Textract\TextractClient([
            'version'   => 'latest',
            'region'    => 'us-east-1',
            'credentials' => [
                'key' => env('AWS_ACCESS_KEY_ID'),
                'secret'=> env('AWS_SECRET_ACCESS_KEY')
            ]
        ]);

        $result = $client->analyzeID([
            'DocumentPages' => [
                [
                    'Bytes' => file_get_contents(public_path($path_dni)),
                ],
            ],
        ]);


        $isDni = false;

        $firstname = '';
        $lastname = '';
        $middlename = '';
        $date_birth = '';

        try{
            foreach($result['IdentityDocuments'][0]['IdentityDocumentFields'] as $itm)
            {   
                if($itm['Type']['Text'] == 'FIRST_NAME') $firstname = $itm['ValueDetection']['Text'];
                if($itm['Type']['Text'] == 'LAST_NAME') $lastname = $itm['ValueDetection']['Text'];
                if($itm['Type']['Text'] == 'MIDDLE_NAME') $middlename = $itm['ValueDetection']['Text'];
                if($itm['Type']['Text'] == 'DATE_OF_BIRTH') $date_birth = $itm['ValueDetection']['Text'];
            }

            foreach($result['IdentityDocuments'][0]['Blocks'] as $itm)
            {
                if(isset($itm['Text']))
                {
                    if($itm['Text'] == 'REPUBLICA BOLIVARIANA DE VENEZUELA') $isDni = true;
                    if($itm['Text'] == 'CEDULA DE IDENTIDAD') $isDni = true;
                }
            }
        }catch(\Exception $e){}
        
        return response()->json([
            'result' => true,
            'isDni' => $isDni,
            'firstName' => $firstname,
            'lastName' => $lastname,
            'middleName' => $middlename,
            'dateBirth' => $date_birth,
            
        ]);

    }

    public function compareFacesAws(Request $request)
    {
        $path_document = null;
        $path_file = null;
        if ($request->hasFile('fileDni') && $request->hasFile('fileEyes')) {
            $path_document = $request->file('fileDni')->store('uploads/media');
            $path_file = $request->file('fileEyes')->store('uploads/media');
        }else{
            return response()->json([
                'result' => false
            ]);
        }


        $client = new \Aws\Rekognition\RekognitionClient([
            'version'   => 'latest',
            'region'    => 'us-east-1',
            'credentials' => [
                'key' => env('AWS_ACCESS_KEY_ID'),
                'secret'=> env('AWS_SECRET_ACCESS_KEY')
            ]
        ]);

        $result = $client->compareFaces([
            'QualityFilter' => 'MEDIUM',
            // 'SimilarityThreshold' => <float>,
            'SourceImage' => [ // REQUIRED
                'Bytes' => file_get_contents(public_path($path_document)),
            ],
            'TargetImage' => [ // REQUIRED
                'Bytes' => file_get_contents(public_path($path_file)),
            ],
        ]);

        $similarity = 0;
        $matchFaces = false;
        try{
        if(count($result['FaceMatches']) > 0)
        {
            foreach($result['FaceMatches'] as $itm)
            {
                if(isset($itm['Similarity']) && $itm['Similarity'] > 80)
                {
                    $similarity = intval($itm['Similarity']);
                    $matchFaces = true;
                }
            }
        }
        }catch(\Exception $e){}


        return response()->json([
            'result' => true,
            'matchFaces' => $matchFaces,
            'similarity' => $similarity,
        ]);
        //dd($result);
    }

    
    public function processOnlyImages()
    {

        $directory = "G:\\Mi unidad\\futbol scantext";
        $images = glob($directory . "\\*.png");
        
        $client = new \Aws\Textract\TextractClient([
            'version'   => 'latest',
            'region'    => 'us-east-1',
            'credentials' => [
                'key' => env('AWS_ACCESS_KEY_ID'),
                'secret'=> env('AWS_SECRET_ACCESS_KEY')
            ]
        ]);

        foreach($images as $key => $path_dni)
        {

            $file_name_obj = explode('\\', $path_dni);
            $onlyname = $file_name_obj[count($file_name_obj) - 1];
            $row = explode('-', $onlyname);

            $info_image = \App\Models\ZProcessImage::where('namefile', $path_dni)->first();
            if(!$info_image)
            {
                $result = $client->analyzeID([
                    'DocumentPages' => [
                        [
                            'Bytes' => file_get_contents($path_dni)
                        ],
                    ],
                ]);
                
                $info_image = new \App\Models\ZProcessImage;
                $info_image->namefile = $path_dni;
                $info_image->result_p = serialize($result);
                $info_image->save();
            }
        }

    }

    public function scanImages($filename = '')
    {
        $directory = "G:\\Mi unidad\\futbol scantext";
        $images = glob($directory . "\\*.png");
        //dd($images);
        $data = '';
      

        $resultado_handicap = false;
        $resultado_handicap_ad = false;
        $linea_de_gol_adicional = false;
        $total_goles_otras_opciones = false;
        $descanso_final_mttc = false;
        $resultado_total_goles = false;

        $lgoles_impar_par = false;
        $vgoles_impar_par = false;
        $pmitad_goles_impar_par = false;
        $ultimo_equipo_anotar = false;

        $gol_temprano = false;
        $gol_tardio = false;
        $momento_primer_gol = false;
        $gol_del_equipo = false;
        $porteria_cero = false;
        
        // $info_image = \App\Models\ZProcessImage::where('namefile', "G:\\Mi unidad\\futbol scantext\\4-2.png")->first();
        // $result = unserialize($info_image->result_p);
        // //dd($result);
        // foreach($result['IdentityDocuments'][0]['Blocks'] as $index => $itm)
        // {

                  

        // }
        // print_r($data);
        // exit();


        /**
         * Start
         */
        $client = new \Aws\Textract\TextractClient([
            'version'   => 'latest',
            'region'    => 'us-east-1',
            'credentials' => [
                'key' => env('AWS_ACCESS_KEY_ID'),
                'secret'=> env('AWS_SECRET_ACCESS_KEY')
            ]
        ]);

        $print_row = 0;
        $first_time = true;

        foreach($images as $key => $path_dni)
        {

            //try {
            $file_name_obj = explode('\\', $path_dni);
            $onlyname = $file_name_obj[count($file_name_obj) - 1];
            $row = explode('-', $onlyname);
            //$data .= '|->page'.$row[1];
            if($print_row != $row[0])
            {
                if(!$first_time)
                {
                    
                    for($xname = 1; $xname < 12; $xname++)
                    {
                        $file_name_obj = explode('\\', $path_dni);
                        $onlyname = $file_name_obj[count($file_name_obj) - 1];
                        $row = explode('-', $onlyname);

                        try{
                            //rename("G:\\Mi unidad\\futbol scantext\\" . $print_row . "-" . $xname . ".png", "G:\\Mi unidad\\futbol scantext processed\\" . $print_row . "-" . $xname . ".png");
                        }catch(\Exception $e){}
                    }

                    create_file($filename, $data);
                    $data = '';

                    
                    $resultado_handicap = false;
                    $resultado_handicap_ad = false;
                    $linea_de_gol_adicional = false;
                    $total_goles_otras_opciones = false;
                    $descanso_final_mttc = false;
                    $resultado_total_goles = false;

                    $lgoles_impar_par = false;
                    $vgoles_impar_par = false;
                    $pmitad_goles_impar_par = false;
                    $ultimo_equipo_anotar = false;

                    $gol_temprano = false;
                    $gol_tardio = false;
                    $momento_primer_gol = false;
                    $gol_del_equipo = false;
                    $porteria_cero = false;

                    
                    //print_r($data);
                    //exit();
                }
                
                $print_row = $row[0];
                $data .= $row[0];

                $first_time = false;
            }

            
            // if($key == 600)
            // {
            //     //print_r($data);
            //     exit();
            // }
            


            $info_image = \App\Models\ZProcessImage::where('namefile', $path_dni)->first();
            if($info_image)
            {
                $result = unserialize($info_image->result_p);
                //dd($result);
            }else{
                $result = $client->analyzeID([
                    'DocumentPages' => [
                        [
                            'Bytes' => file_get_contents($path_dni)
                        ],
                    ],
                ]);
                //dd($result);
                $info_image = new \App\Models\ZProcessImage;
                $info_image->namefile = $path_dni;
                $info_image->result_p = serialize($result);
                $info_image->save();
            }

            
            //dd($result);

            foreach($result['IdentityDocuments'][0]['Blocks'] as $index => $itm)
            {
                #region Resultado final
                if(isset($itm['Text']) && (strtolower($itm['Text']) == strtolower('Resultado final'))){
                    $contador_faltantes = 3;
                    for($x = $index; $x < ($index + 8); $x++){
                        if(str_contains(strtolower(explode(' ', $result['IdentityDocuments'][0]['Blocks'][$x]['Text'])[0]), 'empate')){
                            if($contador_faltantes == 0) break;
                            foreach(explode(' ', $result['IdentityDocuments'][0]['Blocks'][$x-1]['Text']) as $itm){
                                if(is_numeric($itm)) {$data .= ',' . $itm;$contador_faltantes--;}
                            }
                            foreach(explode(' ', $result['IdentityDocuments'][0]['Blocks'][$x]['Text']) as $itm){
                                if(is_numeric($itm)) {$data .= ',' . $itm;$contador_faltantes--;}
                            }
                            foreach(explode(' ', $result['IdentityDocuments'][0]['Blocks'][$x+1]['Text']) as $itm){
                                if(is_numeric($itm)) {$data .= ',' . $itm;$contador_faltantes--;}
                            }
                        }
                    }
                    for($fal = 0;$fal < $contador_faltantes; $fal++) $data .= ',';
                }
                #endregion

                #region Doble oportunidad
                if(isset($itm['Text']) && (strtolower($itm['Text']) == strtolower('Doble oportunidad'))){
                    $contador_faltantes = 3;
                    for($x = ($index+1); $x < count($result['IdentityDocuments'][0]['Blocks']); $x++){
                        if(is_numeric($result['IdentityDocuments'][0]['Blocks'][$x]['Text']) && (count(explode('.', $result['IdentityDocuments'][0]['Blocks'][$x]['Text'])) > 1)) {
                            if($contador_faltantes == 0)break;

                            $data .= ',' . $result['IdentityDocuments'][0]['Blocks'][$x]['Text']; 
                            $contador_faltantes--; 
                        }else{
                            $var_dump = explode(' ', $result['IdentityDocuments'][0]['Blocks'][$x]['Text']);
                            if(is_numeric($var_dump[count($var_dump) - 1]))
                            {
                                if($contador_faltantes == 0)break;

                                $data .= ',' . $var_dump[count($var_dump) - 1]; 
                                $contador_faltantes--; 
                            }
                        }
                    }
                    for($fal = 0;$fal < $contador_faltantes; $fal++) $data .= ',FALTA';
                }
                #endregion
  

                #region Goles - Más/Menos de
                if(isset($itm['Text']) && (strtolower($itm['Text']) == strtolower('Goles - Más/Menos de'))){
                    for($x = ($index+2); $x < ($index+5); $x++){
                        if(strtolower($result['IdentityDocuments'][0]['Blocks'][$x]['Text']) == strtolower('Menos de')){
                            $data .= ',' . $result['IdentityDocuments'][0]['Blocks'][$x+2]['Text'];
                            $data .= ',' . $result['IdentityDocuments'][0]['Blocks'][$x+3]['Text'];
                            break;
                            // print_r($result['IdentityDocuments'][0]['Blocks'][$x+2]['Text']);print_r("<br>");
                            // print_r($result['IdentityDocuments'][0]['Blocks'][$x+3]['Text']);print_r("<br>");
                        }
                    }
                }
                #endregion

                #region Ambos equipos anotarán  
                if(isset($itm['Text']) && (strtolower($itm['Text']) == strtolower('Ambos equipos anotarán'))){
                    for($x = ($index+1); $x < ($index+6); $x++){
                        $var_dump = explode(' ', $result['IdentityDocuments'][0]['Blocks'][$x]['Text']);
                        if($var_dump > 1 && strtolower($var_dump[0]) == strtolower('No')){
                            $var_dump_a = explode(' ', $result['IdentityDocuments'][0]['Blocks'][$x-1]['Text']);
                            $data .= ',' . ((count($var_dump_a) > 1) ? $var_dump_a[1] : $var_dump_a[0]);
                            $data .= ',' . explode(' ', $result['IdentityDocuments'][0]['Blocks'][$x]['Text'])[1];
                            break;
                        }
                    }
                }
                #endregion
                
                #region Resultado/ambos equipos anotarán
                if(isset($itm['Text']) && (strtolower($itm['Text']) == strtolower('Resultado/ambos equipos anotarán'))){
                    $contador_faltantes = 3;
                    for($x = ($index+3); $x < count($result['IdentityDocuments'][0]['Blocks']); $x++){
                        if(is_numeric($result['IdentityDocuments'][0]['Blocks'][$x]['Text']) && !is_numeric($result['IdentityDocuments'][0]['Blocks'][$x-1]['Text'])){
                            if($contador_faltantes == 0)break;
                            $data .= ',' . $result['IdentityDocuments'][0]['Blocks'][$x]['Text'];
                            $data .= ',' . $result['IdentityDocuments'][0]['Blocks'][$x+1]['Text'];
                            $contador_faltantes--; 
                        }
                    }
                    for($fal = 0;$fal < $contador_faltantes; $fal++) $data .= ',FALTA';
                }
                #endregion
                
                //--REVISAR DUPLICO LOS PRIMEROS 2 -----OK
                #region Descanso/Final || Medio Tiempo/Tiempo Completo
                if(isset($itm['Text']) && in_array(strtolower($itm['Text']), array(strtolower('Descanso/Final'), strtolower('Medio Tiempo/Tiempo Completo')))){
                    if(!$descanso_final_mttc){
                        $contador_faltantes = 9;
                        for($x = ($index+1); $x < count($result['IdentityDocuments'][0]['Blocks']); $x++){
                            if(is_numeric($result['IdentityDocuments'][0]['Blocks'][$x]['Text']) && (count(explode('.', $result['IdentityDocuments'][0]['Blocks'][$x]['Text'])) > 1)) {
                                if($contador_faltantes == 0)break;

                                $data .= ',' . $result['IdentityDocuments'][0]['Blocks'][$x]['Text']; 
                                $contador_faltantes--; 
                                $descanso_final_mttc=true;
                            }else{
                                $var_dump = explode(' ', $result['IdentityDocuments'][0]['Blocks'][$x]['Text']);
                                if(is_numeric($var_dump[count($var_dump) - 1]))
                                {
                                    if($contador_faltantes == 0)break;

                                    $data .= ',' . $var_dump[count($var_dump) - 1]; 
                                    $contador_faltantes--; 
                                    $descanso_final_mttc=true;
                                }
                            }
                        }
                        for($fal = 0;$fal < $contador_faltantes; $fal++) $data .= ',FALTA';
                    }
                }
                #endregion
                
                #region Línea de gol
                if(isset($itm['Text']) && (strtolower($itm['Text']) == strtolower('Línea de gol'))){
                    for($x = ($index+2); $x < ($index+5); $x++){
                        if(strtolower($result['IdentityDocuments'][0]['Blocks'][$x]['Text']) == strtolower('Menos de')){
                            $data .= ',' . $result['IdentityDocuments'][0]['Blocks'][$x+2]['Text'];
                            $data .= ',' . $result['IdentityDocuments'][0]['Blocks'][$x+3]['Text'];
                            break;
                        }
                    }
                }
                #endregion
                
                #region Empate - Apuesta no válida
                if(isset($itm['Text']) && in_array(strtolower($itm['Text']), array(strtolower('Empate - Apuesta no válida'), strtolower('Empate . Apuesta no válida')))){
                    $contador_faltantes = 2;
                    for($x = ($index); $x < count($result['IdentityDocuments'][0]['Blocks']); $x++){
                        $obj_temp = explode(' ', strtolower($result['IdentityDocuments'][0]['Blocks'][$x]['Text']));
                        if($obj_temp > 1 && is_numeric(str_replace(',', '.', $obj_temp[count($obj_temp) - 1]))){
                            if($contador_faltantes == 0) break;
                            $data .= ',' . str_replace(',', '.', $obj_temp[count($obj_temp) - 1]);
                            $contador_faltantes--;
                        }
                    }
                    for($fal = 0;$fal < $contador_faltantes; $fal++) $data .= ',Falta';
                }
                #endregion
    
                #region Resultado con hándicap
                if(isset($itm['Text']) && (strtolower($itm['Text']) == strtolower('Resultado con hándicap'))){
                    if(!$resultado_handicap){
                        $resultado_handicap = true;
                        $contador_faltantes = 3;
                        for($x = ($index + 4); $x < count($result['IdentityDocuments'][0]['Blocks']); $x++){
                            $var_dump = explode(' ', strtolower($result['IdentityDocuments'][0]['Blocks'][$x]['Text']));
                            if(count($var_dump) > 1 && is_numeric($var_dump[1])){
                                if($contador_faltantes == 0) break;
                                $data .= ',' . explode(' ', strtolower($result['IdentityDocuments'][0]['Blocks'][$x]['Text']))[1];
                                $contador_faltantes--;
                            }
                        }
                        for($fal = 0;$fal < $contador_faltantes; $fal++) $data .= ',';
                    }
                }
                #endregion

                #region Resultado con hándicap - Adicionales
                if(isset($itm['Text']) && (strtolower($itm['Text']) == strtolower('Resultado con hándicap - Adicionales'))){
                    if(!$resultado_handicap_ad){
                        $resultado_handicap_ad = true;
                        $contador_faltantes = 12;
                        
                        $data_l = '';
                        $data_e = '';
                        $data_i = '';
                        $count = 0;
                        for($x = ($index); $x < count($result['IdentityDocuments'][0]['Blocks']); $x++){
                            
                            $var_xp = explode(' ', strtolower($result['IdentityDocuments'][0]['Blocks'][$x]['Text']));
                            if(($result['IdentityDocuments'][0]['Blocks'][$x]['BlockType'] == 'LINE') && count($var_xp) > 1 && is_numeric(str_replace(',','.', $var_xp[1]))){
                                if($contador_faltantes == 0) break;
                                if ($count == 0) {
                                    $data_l .= ',' . str_replace(',','.', $var_xp[1]);
                                }else if ($count == 1) {
                                    $data_e .= ',' . str_replace(',','.', $var_xp[1]);
                                }else{
                                    $data_i .= ',' . str_replace(',','.', $var_xp[1]);
                                    $count = -1;
                                }
                                $count++;
                                $contador_faltantes--;
                            }else{
                                $var_dato = str_replace(['-3','-2','-1','+1','+2','+3'],'', strtolower($result['IdentityDocuments'][0]['Blocks'][$x]['Text']));
                                if(($result['IdentityDocuments'][0]['Blocks'][$x]['BlockType'] == 'LINE') && is_numeric($var_dato)){
                                    if($contador_faltantes == 0) break;
                                    if ($count == 0) {
                                        $data_l .= ',' . str_replace(',','.', $var_dato);
                                    }else if ($count == 1) {
                                        $data_e .= ',' . str_replace(',','.', $var_dato);
                                    }else{
                                        $data_i .= ',' . str_replace(',','.', $var_dato);
                                        $count = -1;
                                    }
                                    $count++;
                                    $contador_faltantes--;
                                }
                            }
                        }
                        $data .= $data_l . $data_e . $data_i;
                        for($fal = 0;$fal < $contador_faltantes; $fal++) $data .= ',FALTA';
                    }
                }
                #endregion

                //--REVISAR POSICION DE CELDAS VACIAS -----OK
                #region Línea de gol - Adicional
                $lga_arra = array(
                    '0.5',
                    '0.5, 1.0',
                    '1.0',
                    '1.0, 1.5',
                    '1.5',
                    '1.5, 2.0',
                    '2.0',
                    '2.0, 2.5',
                    '2.5',
                    '2.5, 3.0',
                    '3.0',
                    '3.0, 3.5',
                    '3.5',
                    '3.5, 4.0',
                    '4.0',
                    '4.0, 4.5',
                    '4.5',
                    '4.5, 5.0',
                    '5.0',
                    '5.0, 5.5',
                    '5.5',
                    '5.5, 6.0',
                    '6.0',
                    '6.0, 6.5',
                    '6.5',
                    '6.5, 7.0',
                    '7.0',
                    '7.0, 7.5',
                );
                
                if((isset($itm['Text']) && $itm["BlockType"] == "LINE") && in_array(strtolower($itm['Text']), array(strtolower('Línea de gol - Adicional')))){
                    $data_one = array();
                    $last_index = 0;
                    foreach($lga_arra as $indx => $obj_one)
                    {
                        $found = false;
                        for($x = ($index); $x < (count($result['IdentityDocuments'][0]['Blocks'])); $x++){
                            $var_exp = str_replace(',', '.', str_replace(' ', '', strtolower($result['IdentityDocuments'][0]['Blocks'][$x]['Text'])));
                            if(strtolower($result['IdentityDocuments'][0]['Blocks'][$x]["BlockType"]) == strtolower("LINE") && (($x > $last_index) && ($var_exp == str_replace(',', '.', str_replace(' ', '', $obj_one)))) && is_numeric($result['IdentityDocuments'][0]['Blocks'][$x+1]['Text']) && is_numeric($result['IdentityDocuments'][0]['Blocks'][$x+2]['Text']))
                            {
                                $last_index = $x;
                                $found = true;
                                $data_one[] = $result['IdentityDocuments'][0]['Blocks'][$x+1]['Text'];
                                $data_one[] = $result['IdentityDocuments'][0]['Blocks'][$x+2]['Text'];
                                break;
                            }
                        }
                        if(!$found)
                        {
                            $data_one[] = '';
                            $data_one[] = '';
                        }
                    }
                    $data .= ','. str_replace('$','', implode(',', $data_one));
                }

                #endregion
            
                #region Resultado en el descanso
                if(isset($itm['Text']) && (strtolower($itm['Text']) == strtolower('Resultado en el descanso'))){
                  // print_r('Resultado en el descanso');print_r("<br>");
                    for($x = $index; $x < ($index + 6); $x++){
                        if(str_contains(strtolower(explode(' ', $result['IdentityDocuments'][0]['Blocks'][$x]['Text'])[0]), 'empate')){
                            foreach(explode(' ', $result['IdentityDocuments'][0]['Blocks'][$x-1]['Text']) as $itm){
                                if(is_numeric($itm)) {$data .= ',' . $itm;/*print_r($itm);print_r("<br>");*/}
                            }
                            foreach(explode(' ', $result['IdentityDocuments'][0]['Blocks'][$x]['Text']) as $itm){
                                if(is_numeric($itm)) {$data .= ',' . $itm;/*print_r($itm);print_r("<br>");*/}
                            }
                            foreach(explode(' ', $result['IdentityDocuments'][0]['Blocks'][$x+1]['Text']) as $itm){
                                if(is_numeric($itm)) {$data .= ',' . $itm;/*print_r($itm);print_r("<br>");*/}
                            }
                        }
                    }
                }
                #endregion

                #region Descanso - Doble oportunidad
                if(isset($itm['Text']) && (strtolower($itm['Text']) == strtolower('Descanso - Doble oportunidad'))){
                    $contador_faltantes = 3;
                    for($x = $index + 1; $x < count($result['IdentityDocuments'][0]['Blocks']); $x++){
                        $var_dump = explode(' ', $result['IdentityDocuments'][0]['Blocks'][$x]['Text']);
                        foreach($var_dump as $iitm){
                            if(is_numeric($iitm)) {
                                if($contador_faltantes == 0) break;
                                $data .= ',' . $iitm;
                                $contador_faltantes--;
                            }
                        }
                        if($contador_faltantes == 0) break;
                    }
                    for($fal = 0;$fal < $contador_faltantes; $fal++) $data .= ',Falta';
                }
                #endregion
                
                #region Descanso - Resultado/ambos equipos anotarán
                if(isset($itm['Text']) && (strtolower($itm['Text']) == strtolower('Descanso - Resultado/ambos equipos anotarán'))){
                  // print_r('Descanso - Resultado/ambos equipos anotarán');print_r("<br>");
                    for($x = ($index+3); $x < ($index+12); $x++){
                        if(is_numeric($result['IdentityDocuments'][0]['Blocks'][$x]['Text']) && !is_numeric($result['IdentityDocuments'][0]['Blocks'][$x-1]['Text'])){
                            $data .= ',' . $result['IdentityDocuments'][0]['Blocks'][$x]['Text'];
                            $data .= ',' . $result['IdentityDocuments'][0]['Blocks'][$x+1]['Text'];
                            // print_r($result['IdentityDocuments'][0]['Blocks'][$x]['Text']);print_r("<br>");
                            // print_r($result['IdentityDocuments'][0]['Blocks'][$x+1]['Text']);print_r("<br>");
                        }
                    }
                }
                #endregion
                
                #region Descanso - Resultado/total de goles
                if(isset($itm['Text']) && (strtolower($itm['Text']) == strtolower('Descanso - Resultado/total de goles'))){
                  // print_r('Descanso - Resultado/total de goles');print_r("<br>");
                    for($x = ($index+3); $x < ($index+12); $x++){
                        if(is_numeric($result['IdentityDocuments'][0]['Blocks'][$x]['Text']) && !is_numeric($result['IdentityDocuments'][0]['Blocks'][$x-1]['Text'])){
                            $data .= ',' . $result['IdentityDocuments'][0]['Blocks'][$x]['Text'];
                            $data .= ',' . $result['IdentityDocuments'][0]['Blocks'][$x+1]['Text'];
                            // print_r($result['IdentityDocuments'][0]['Blocks'][$x]['Text']);print_r("<br>");
                            // print_r($result['IdentityDocuments'][0]['Blocks'][$x+1]['Text']);print_r("<br>");
                        }
                    }
                }
                #endregion

                #region Mitad con más goles
                if(isset($itm['Text']) && (strtolower($itm['Text']) == strtolower('Mitad con más goles'))){
                  // print_r('Mitad con más goles');print_r("<br>");
                    for($x = $index; $x < ($index + 5); $x++){
                        $var_exp = explode(' ', $result['IdentityDocuments'][0]['Blocks'][$x]['Text']);
                        if(count($var_exp) > 1 && str_contains(strtolower($var_exp[0]), '2') && str_contains(strtolower($var_exp[1]), 'mitad')){
                            foreach(explode(' ', $result['IdentityDocuments'][0]['Blocks'][$x-1]['Text']) as $key => $itm){
                                if($key>0 && is_numeric($itm)) {$data .= ',' . $itm;/*print_r($itm);print_r("<br>");*/}
                            }
                            foreach(explode(' ', $result['IdentityDocuments'][0]['Blocks'][$x]['Text']) as $key => $itm){
                                if($key>0 && is_numeric($itm)) {$data .= ',' . $itm;/*print_r($itm);print_r("<br>");*/}
                            }
                            foreach(explode(' ', $result['IdentityDocuments'][0]['Blocks'][$x+1]['Text']) as $key => $itm){
                                if($key>0 && is_numeric($itm)) {$data .= ',' . $itm;/*print_r($itm);print_r("<br>");*/}
                            }
                        }
                    }
                }
                #endregion
                
                //REVISAR POSICION DE CELDAS VACIAS
                #region Total de goles - Otras opciones
                // if(isset($itm['Text']) && (strtolower($itm['Text']) == strtolower('Total de goles - Otras opciones'))){
                //     if(!$total_goles_otras_opciones){
                //         $total_goles_otras_opciones = true;
                //       // print_r('Total de goles - Otras opciones');print_r("<br>");
                //         for($x = ($index); $x < ($index + 7); $x++){
                //             if(strtolower($result['IdentityDocuments'][0]['Blocks'][$x]['Text']) == strtolower('Menos de'))
                //             {
                //                 $count = 0;
                //                 for($y = ($x + 1); $y <= (count($result['IdentityDocuments'][0]['Blocks'])); $y++){
                //                     if(strtolower($result['IdentityDocuments'][0]['Blocks'][$y]['Text']) == strtolower('Resultado/Total de goles')) break;
                //                     if($count > 0)
                //                     {
                //                         if(!is_numeric($result['IdentityDocuments'][0]['Blocks'][$y]['Text'])) break;
                //                         $data .= ',' . $result['IdentityDocuments'][0]['Blocks'][$y]['Text'];
                //                         // print_r($result['IdentityDocuments'][0]['Blocks'][$y]['Text']);print_r("<br>");
                //                         if($count == 2) $count = -1;
                //                     }
                //                     $count++;
                //                 }
                //                 break;
                //             }
                //         }
                //     }
                // }


                $equipo_total_goles_l = array(
                    '0.5',
                    '1.5',
                    //'2.5',
                    '3.5',
                    '4.5',
                    '5.5',
                    '6.5',
                    '7.5',
                    '8.5',
                    '9.5',
                    '10.5',
                    '11.5',
                );

                if(isset($itm['Text']) && (strtolower($itm['Text']) == strtolower('Total de goles - Otras opciones'))){
                    //print_r('Total de goles - Otras opciones');print_r("<br>");

                    $data_one = array();
                    foreach($equipo_total_goles_l as $indx => $obj_one)
                    {
                        $found = false;
                        for($x = ($index); $x < ($index + 38); $x++){
                            $var_exp = str_replace(',','.',$result['IdentityDocuments'][0]['Blocks'][$x]['Text']);
                            
                            if(strlen($var_exp) == 3 && $var_exp == $obj_one)
                            {
                                $found = true;

                                $data_one[] = str_replace(',','.',$result['IdentityDocuments'][0]['Blocks'][$x + 1]['Text']);
                                $data_one[] = str_replace(',','.',$result['IdentityDocuments'][0]['Blocks'][$x + 2]['Text']);
                                
                                break;
                            }
                        }
                        if(!$found)
                        {
                            $data_one[] = '';
                            $data_one[] = '';
                            
                        }
                    }
                    $data .= ','.implode(',', $data_one);
                }


                #endregion
            
                //--REVISAR CANTIDAD DE CAMPOS LLENADOS 6 -----OK
                
                #region Resultado/total de goles
                if(isset($itm['Text']) && (strtolower($itm['Text']) == strtolower('Resultado/total de goles'))){
                    // print_r('Resultado/total de goles');print_r("<br>");
                    if(!$resultado_total_goles)
                    {
                        $resultado_total_goles = true;
                        $contador_faltantes = 6;
                        for($x = ($index+1); $x < ($index+15); $x++){
                            //print_r($result['IdentityDocuments'][0]['Blocks'][$x]['Text']);print_r("<br>");
                            if(strtolower($result['IdentityDocuments'][0]['Blocks'][$x]['Text']) == strtolower('Total de goles/ambos equipos anotarán')) break;
                            $var_exp = explode(' ', $result['IdentityDocuments'][0]['Blocks'][$x]['Text']);
                            if(count($var_exp) > 1 && is_numeric($var_exp[1])){
                                $data .= ',' . $var_exp[1];
                                $contador_faltantes--;
                                //print_r($var_exp[1]);print_r("<br>");
                            }else{
                                $var_data = str_replace('2.5','', $result['IdentityDocuments'][0]['Blocks'][$x]['Text']);
                                if(is_numeric($var_data))
                                {
                                    $data .= ',' . $var_data;
                                    $contador_faltantes--;
                                }
                            }
                        }
                    }
                    for($fal = 0;$fal < $contador_faltantes; $fal++) $data .= ',';
                }
                #endregion

                #region Total de goles/ambos equipos anotarán
                if(isset($itm['Text']) && (strtolower($itm['Text']) == strtolower('Total de goles/ambos equipos anotarán'))){
                  // print_r('Total de goles/ambos equipos anotarán');print_r("<br>");
                    for($x = ($index); $x < ($index+5); $x++){
                        $var_exp = explode(' ', strtolower($result['IdentityDocuments'][0]['Blocks'][$x]['Text']));
                        if(count($var_exp) > 3 && is_numeric($var_exp[count($var_exp) - 1])){
                            $data .= ',' . $var_exp[count($var_exp) - 1];
                            //print_r($var_exp[count($var_exp) - 1]);print_r("<br>");
                        }
                    }
                }
                #endregion

                //REVISAR ORDEN DE EXTRACCION -----OK
                #region Número total exacto de goles
                if(isset($itm['Text']) && (strtolower($itm['Text']) == strtolower('Número total exacto de goles'))){
                    // print_r('Número total exacto de goles');print_r("<br>");
                    $data_a = '';
                    $data_b = '';
                    $count = 0;
                    for($x = ($index); $x < ($index+20); $x++){
                        if(strtolower($result['IdentityDocuments'][0]['Blocks'][$x]['Text']) == strtolower('Número de goles en el partido')) break;
                        if(is_numeric($result['IdentityDocuments'][0]['Blocks'][$x]['Text']) && !is_numeric($result['IdentityDocuments'][0]['Blocks'][$x-1]['Text'])){



                            if ($count == 0) {
                                $data_a .= ',' . $result['IdentityDocuments'][0]['Blocks'][$x]['Text'];
                            }else{
                                $data_b .= ',' . $result['IdentityDocuments'][0]['Blocks'][$x]['Text'];
                                $count = -1;
                            }
                            $count++;


                            //$data .= ',' . $result['IdentityDocuments'][0]['Blocks'][$x]['Text'];


                            // print_r($result['IdentityDocuments'][0]['Blocks'][$x]['Text']);print_r("<br>");
                            //print_r($result['IdentityDocuments'][0]['Blocks'][$x+1]['Text']);print_r("<br>");
                        }
                    }
                    $data .= $data_a . $data_b;
                }
                #endregion

                #region Número de goles en el partido
                if(isset($itm['Text']) && (strtolower($itm['Text']) == strtolower('Número de goles en el partido'))){
                  // print_r('Número de goles en el partido');print_r("<br>");
                    for($x = ($index); $x < ($index+5); $x++){
                        $var_exp = explode(' ', strtolower($result['IdentityDocuments'][0]['Blocks'][$x]['Text']));
                        if(count($var_exp) > 2 && is_numeric($var_exp[count($var_exp) - 1])){
                            $data .= ',' . $var_exp[count($var_exp) - 1];
                            // print_r($var_exp[count($var_exp) - 1]);print_r("<br>");
                        }
                    }
                }
                #endregion

                // #region Ambos equipos anotarán
                // if(isset($itm['Text']) && (strtolower($itm['Text']) == strtolower('Ambos equipos anotarán'))){
                //   // print_r('Ambos equipos anotarán');print_r("<br>");
                //     for($x = ($index); $x < ($index+5); $x++){
                //         if(strtolower($result['IdentityDocuments'][0]['Blocks'][$x]['Text']) == strtolower('Equipos que anotarán')) break;
                //         $var_exp = explode(' ', strtolower($result['IdentityDocuments'][0]['Blocks'][$x]['Text']));
                //         if(count($var_exp) > 1 && is_numeric($var_exp[count($var_exp) - 1])){
                //           // print_r($var_exp[count($var_exp) - 1]);print_r("<br>");
                //         }
                //     }
                // }
                // #endregion

                #region Equipos que anotarán
                if(isset($itm['Text']) && (strtolower($itm['Text']) == strtolower('Equipos que anotarán'))){
                  // print_r('Equipos que anotarán');print_r("<br>");
                    for($x = ($index); $x < ($index+8); $x++){
                        if(strtolower($result['IdentityDocuments'][0]['Blocks'][$x]['Text']) == strtolower('1ª mitad - Ambos equipos anotarán')) break;
                        $var_exp = explode(' ', strtolower($result['IdentityDocuments'][0]['Blocks'][$x]['Text']));
                        if(count($var_exp) > 2 && is_numeric($var_exp[count($var_exp) - 1])){
                            $data .= ',' . $var_exp[count($var_exp) - 1];
                            // print_r($var_exp[count($var_exp) - 1]);print_r("<br>");
                        }
                    }
                }
                #endregion

                #region 1ª mitad - Ambos equipos anotarán    
                if(isset($itm['Text']) && in_array(strtolower($itm['Text']), array(strtolower('1ª mitad - Ambos equipos anotarán'), strtolower('1ª mitad . Ambos equipos anotarán')))){
                    $contador_faltantes = 2;
                    for($x = ($index+1); $x < ($index+5); $x++){
                        $var_exp = explode(' ', strtolower($result['IdentityDocuments'][0]['Blocks'][$x]['Text']));
                        if(count($var_exp) > 1 && is_numeric(str_replace(',','.', $var_exp[count($var_exp) - 1]))){
                            if($contador_faltantes == 0) break;
                            $data .= ',' . str_replace(',','.', $var_exp[count($var_exp) - 1]);
                            $contador_faltantes--;
                        }
                    }
                }
                #endregion

                #region 2ª mitad - Ambos equipos anotarán
                if(isset($itm['Text']) && in_array(strtolower($itm['Text']), array(strtolower('2ª mitad - Ambos equipos anotarán'), strtolower('2 mitad - Ambos equipos anotarán')))){
                    $contador_faltantes = 2;
                    for($x = ($index+1); $x < ($index+5); $x++){
                        $var_exp = explode(' ', strtolower($result['IdentityDocuments'][0]['Blocks'][$x]['Text']));
                        if(count($var_exp) > 1 && is_numeric(str_replace(',','.', $var_exp[count($var_exp) - 1]))){
                            if($contador_faltantes == 0) break;
                            $data .= ',' . str_replace(',','.', $var_exp[count($var_exp) - 1]);
                            $contador_faltantes--;
                        }
                    }
                    for($fal = 0;$fal < $contador_faltantes; $fal++) $data .= ',FALTA';
                }
                #endregion

                #region Ambos equipos anotarán en la 1ª mitad - 2ª mitad    
                if(isset($itm['Text']) && in_array(strtolower($itm['Text']), array(strtolower('Ambos equipos anotarán en la 1ª mitad - 2ª mitad'), strtolower('Ambos equipos anotarán en la 1ª mitad - 2 mitad')))){
                    $contador_faltantes = 4;
                    for($x = ($index+1); $x < ($index+6); $x++){
                        $var_exp = explode(' ', strtolower($result['IdentityDocuments'][0]['Blocks'][$x]['Text']));
                        if(count($var_exp) > 1 && is_numeric(str_replace(',','.', $var_exp[count($var_exp) - 1]))){
                            if($contador_faltantes == 0) break;
                            $data .= ',' . str_replace(',','.', $var_exp[count($var_exp) - 1]);
                            $contador_faltantes--;
                        }
                    }
                    for($fal = 0;$fal < $contador_faltantes; $fal++) $data .= ',FALTA';
                }
                #endregion

                //REVISAR POSICION DE CELDAS VACIAS ------OK
                #region 1° tiempo - Goles
                $primer_tiempo_goles = array(
                    '0.5',
                    '1.5',
                    '2.5',
                    '3.5',
                    '4.5',
                    '5.5'
                );
                if(isset($itm['Text']) && in_array(strtolower($itm['Text']), array(strtolower('1° tiempo - Goles'), strtolower('1° tiempo Goles')))){
                    // print_r('1° tiempo - Goles');print_r("<br>");
                    $data_tmp = '';
                    for($x = ($index+1); $x < ($index+5); $x++){
                        if(strtolower($result['IdentityDocuments'][0]['Blocks'][$x]['Text']) == strtolower('Menos de'))
                        {
                            $count = 0;
                            $inicio_b = false;
                            $inicio = 0;
                            $fin = 0;
                            $count_key = 0;
                            
                            for($y = ($x+1); $y <= (count($result['IdentityDocuments'][0]['Blocks'])); $y++){
                                if($count > 0)
                                {
                                    $dato_parse = str_replace(',','.',$result['IdentityDocuments'][0]['Blocks'][$y]['Text']);
                                    if(!is_numeric($dato_parse)) break;

                                    $data_tmp .= ',' . $dato_parse;

                                    foreach($primer_tiempo_goles as $key => $iittmm)
                                    {
                                        $dato_parse_c = str_replace(',','.',$result['IdentityDocuments'][0]['Blocks'][$y-$count]['Text']);
                                        if(str_replace(' ','',str_replace('.','',$iittmm)) == str_replace(' ','',str_replace('.','',$dato_parse_c)))
                                        {
                                            //$data_tmp .= '#fin-'.$key.'#';
                                            $fin = $key;
                                        }
                                    }

                                    // print_r($result['IdentityDocuments'][0]['Blocks'][$y]['Text']);print_r("<br>");
                                    if($count == 2) $count = -1;
                                }else{
                                    if(!$inicio_b)
                                    {
                                        foreach($primer_tiempo_goles as $key => $iittmm)
                                        {
                                            $dato_parse_c = str_replace(',','.',$result['IdentityDocuments'][0]['Blocks'][$y]['Text']);
                                            if(str_replace(' ','',str_replace('.','',$iittmm)) == str_replace(' ','',str_replace('.','',$dato_parse_c)))
                                            {
                                                //$data_tmp .= '#ini-'.$key.'#';
                                                $count_key = $key;
                                                $inicio = $key;
                                                $inicio_b = true;
                                            }
                                        }
                                    }else{
                                        $count_key++;
                                        $dato_parse_c = str_replace(',','.',$result['IdentityDocuments'][0]['Blocks'][$y]['Text']);
                                        if(isset($primer_tiempo_goles[$count_key]) && str_replace(' ','',$primer_tiempo_goles[$count_key]) != str_replace(' ','',$dato_parse_c))
                                        {
                                            $valid = str_replace(',','.',$result['IdentityDocuments'][0]['Blocks'][$y+1]['Text']);
                                            if(is_numeric($valid))
                                            {
                                                $data_tmp .= ',,';
                                                $count_key++;
                                            }
                                        }
                                    }
                                }
                                $count++;
                            }
                            break;
                        }
                    }
                        
                    for($i = 0; $i < $inicio; $i++)
                    {
                        $data .= ',,';
                    }
                    $data .= $data_tmp;

                    for($i = ($fin + 1); $i < (count($primer_tiempo_goles) - $inicio); $i++)
                    {
                        $data .= ',,';
                    }
                    //$data .= '<-|';
                
                }
                #endregion
                
                //REVISAR ORDEN DE EXTRACCION
                #region 1ª mitad - Número exacto de goles
                if(isset($itm['Text']) && (strtolower($itm['Text']) == strtolower('1ª mitad - Número exacto de goles'))){
                    $data_a = '';
                    $data_b = '';
                    $count = 0;
                  // print_r('1ª mitad - Número exacto de goles');print_r("<br>");
                    for($x = ($index); $x < ($index+15); $x++){
                        if(strtolower($result['IdentityDocuments'][0]['Blocks'][$x]['Text']) == strtolower('Total de los minutos de los goles')) break;
                        if(is_numeric($result['IdentityDocuments'][0]['Blocks'][$x]['Text']) && !is_numeric($result['IdentityDocuments'][0]['Blocks'][$x-1]['Text'])){

                            
                            if ($count == 0) {
                                $data_a .= ',' . $result['IdentityDocuments'][0]['Blocks'][$x]['Text'];
                            }else{
                                $data_b .= ',' . $result['IdentityDocuments'][0]['Blocks'][$x]['Text'];
                                $count = -1;
                            }
                            $count++;

                            
                            //$data .= ',' . $result['IdentityDocuments'][0]['Blocks'][$x]['Text'];


                                // print_r($result['IdentityDocuments'][0]['Blocks'][$x]['Text']);print_r("<br>");
                            //print_r($result['IdentityDocuments'][0]['Blocks'][$x+1]['Text']);print_r("<br>");
                        }
                    }
                    $data .= $data_a . $data_b;
                }
                #endregion

                #region Primer equipo que anotará || 1° equipo en anotar
                if(isset($itm['Text']) && (strtolower($itm['Text']) == strtolower('Primer equipo que anotará') || strtolower($itm['Text']) == strtolower('1° equipo en anotar'))){
                  // print_r('Primer equipo que anotará');print_r("<br>");
                    $count_sect = 0;
                    for($x = ($index); $x < ($index+8); $x++){
                        if(strtolower($result['IdentityDocuments'][0]['Blocks'][$x]['Text']) == strtolower('Método del primer gol')) break;
                        $var_exp = explode(' ', strtolower($result['IdentityDocuments'][0]['Blocks'][$x]['Text']));
                        if(count($var_exp) > 1 && is_numeric($var_exp[count($var_exp) - 1])){

                            if($count_sect >= 3) break;
                            $data .= ',' . $var_exp[count($var_exp) - 1];
                            $count_sect++;

                            // print_r($var_exp[count($var_exp) - 1]);print_r("<br>");
                        }
                    }
                }
                #endregion

                //REVISAR AGREGAR EL PENULTIMO DATO
                #region Gol temprano
                if(isset($itm['Text']) && (strtolower($itm['Text']) == strtolower('Gol temprano'))){
                    $gol_temprano = true;
                    $count_sect = 0;
                    for($x = ($index); $x < ($index+8); $x++){
                        //if(strtolower($result['IdentityDocuments'][0]['Blocks'][$x]['Text']) == strtolower('Gol tardío')) break;
                        $var_exp = explode(' ', strtolower($result['IdentityDocuments'][0]['Blocks'][$x]['Text']));
                        if(count($var_exp) > 1 && is_numeric($var_exp[count($var_exp) - 1])){
                            if($count_sect >=2) break;

                            $data .= ',' . $var_exp[count($var_exp) - 2];
                            $data .= ',' . $var_exp[count($var_exp) - 1];
                            
                            $count_sect++;

                            // print_r($var_exp[count($var_exp) - 1]);print_r("<br>");
                        }
                    }
                }
                #endregion

                //REVISAR AGREGAR EL PENULTIMO DATO
                #region Gol tardío
                if(isset($itm['Text']) && (strtolower($itm['Text']) == strtolower('Gol tardío'))){
                    $gol_tardio = true;
                    $count_sect = 0;
                    for($x = ($index); $x < ($index+8); $x++){
                        $var_exp = explode(' ', strtolower($result['IdentityDocuments'][0]['Blocks'][$x]['Text']));
                        if(count($var_exp) > 1 && is_numeric($var_exp[count($var_exp) - 1])){
                            if($count_sect >=2) break;
                            $data .= ',' . $var_exp[count($var_exp) - 2];
                            $data .= ',' . $var_exp[count($var_exp) - 1];
                            $count_sect++;
                        }
                    }
                }
                #endregion
                
                //REVISAR ORDEN DE EXTRACCION
                #region Momento del 1° gol
                if(isset($itm['Text']) && (strtolower($itm['Text']) == strtolower('Momento del 1° gol'))){
                    if(!$gol_temprano){
                        $gol_temprano = true;
                        for($fal = 0;$fal < 4; $fal++) $data .= ',FALTA';

                    }
                    if(!$gol_tardio){
                        $gol_tardio = true;
                        for($fal = 0;$fal < 4; $fal++) $data .= ',FALTA';

                    }
                    $momento_primer_gol = true;

                    $data_a = '';
                    $data_b = '';
                    $count = 0;
                    $count_sect = 0;

                    // print_r('Momento del 1° gol');print_r("<br>");
                    for($x = ($index); $x < ($index+25); $x++){
                        if(strtolower($result['IdentityDocuments'][0]['Blocks'][$x]['Text']) == strtolower('2 mitad - Goles') || strtolower($result['IdentityDocuments'][0]['Blocks'][$x]['Text']) == strtolower('2ª mitad - Goles')) break;
                        if(is_numeric($result['IdentityDocuments'][0]['Blocks'][$x]['Text']) && !is_numeric($result['IdentityDocuments'][0]['Blocks'][$x-1]['Text'])){
                            
                            if($count_sect >= 10) break;

                            if ($count == 0) {
                                $data_a .= ',' . $result['IdentityDocuments'][0]['Blocks'][$x]['Text'];
                                $count_sect++;
                            }else{
                                $data_b .= ',' . $result['IdentityDocuments'][0]['Blocks'][$x]['Text'];
                                $count = -1;
                                $count_sect++;
                            }
                            $count++;


                            // $data .= ',' . $result['IdentityDocuments'][0]['Blocks'][$x]['Text'];



                            // print_r($result['IdentityDocuments'][0]['Blocks'][$x]['Text']);print_r("<br>");
                            //print_r($result['IdentityDocuments'][0]['Blocks'][$x+1]['Text']);print_r("<br>");
                        }
                    }
                    $data .= $data_a . $data_b;
                }
                #endregion

                #region 2 mitad - Goles 2ª mitad Goles
                $sedungo_tiempo_goles = array(
                    '0.5',
                    '1.5',
                    '2.5',
                    '3.5',
                    '4.5',
                    '5.5',
                    '6.5'
                );
                if(isset($itm['Text']) && in_array(strtolower($itm['Text']), array(strtolower('2 mitad - Goles'), strtolower('2ª mitad - Goles'), strtolower('2ª mitad Goles'), strtolower('2 mitad Goles')))){
                    // print_r('2ª mitad Goles');
                    if(!$gol_temprano){
                        $gol_temprano = true;
                        for($fal = 0;$fal < 4; $fal++) $data .= ',FALTA';

                    }
                    if(!$gol_tardio){
                        $gol_tardio = true;
                        for($fal = 0;$fal < 4; $fal++) $data .= ',FALTA';

                    }
                    if(!$momento_primer_gol){
                        $momento_primer_gol = true;
                        for($fal = 0;$fal < 10; $fal++) $data .= ',FALTA';

                    }
                    

                    $data_tmp = '';
                    for($x = ($index+1); $x < ($index+5); $x++){
                        if(strtolower($result['IdentityDocuments'][0]['Blocks'][$x]['Text']) == strtolower('Menos de'))
                        {
                            $count = 0;
                            $inicio_b = false;
                            $inicio = 0;
                            $fin = 0;
                            $count_key = 0;

                            for($y = ($x + 1); $y <= (count($result['IdentityDocuments'][0]['Blocks'])); $y++){
                                if($count > 0)
                                {
                                    
                                    $dato_parse = str_replace(',','.',$result['IdentityDocuments'][0]['Blocks'][$y]['Text']);
                                    if(!is_numeric($dato_parse)) break;

                                    $data_tmp .= ',' . $dato_parse;
                                    //$data .= ',' . $result['IdentityDocuments'][0]['Blocks'][$y]['Text'];

                                    foreach($sedungo_tiempo_goles as $key => $iittmm)
                                    {
                                        $dato_parse_c = str_replace(',','.',$result['IdentityDocuments'][0]['Blocks'][$y-$count]['Text']);
                                        if(str_replace(' ','',str_replace('.','',$iittmm)) == str_replace(' ','',str_replace('.','',$dato_parse_c)))
                                        {
                                            //$data_tmp .= '#fin-'.$key.'#';
                                            $fin = $key;
                                        }
                                    }

                                    // print_r($result['IdentityDocuments'][0]['Blocks'][$y]['Text']);print_r("<br>");
                                    if($count == 2) $count = -1;
                                }else{
                                    if(!$inicio_b)
                                    {
                                        foreach($sedungo_tiempo_goles as $key => $iittmm)
                                        {
                                            $dato_parse_c = str_replace(',','.',$result['IdentityDocuments'][0]['Blocks'][$y]['Text']);
                                            if(str_replace(' ','',str_replace('.','',$iittmm)) == str_replace(' ','',str_replace('.','',$dato_parse_c)))
                                            {
                                                //$data_tmp .= '#ini-'.$key.'#';
                                                $count_key = $key;
                                                $inicio = $key;
                                                $inicio_b = true;
                                            }
                                        }
                                    }else{
                                        $count_key++;
                                        $dato_parse_c = str_replace(',','.',$result['IdentityDocuments'][0]['Blocks'][$y]['Text']);
                                        if(isset($sedungo_tiempo_goles[$count_key]) && str_replace(' ','',$sedungo_tiempo_goles[$count_key]) != str_replace(' ','',$dato_parse_c))
                                        {
                                            $valid = str_replace(',','.',$result['IdentityDocuments'][0]['Blocks'][$y+1]['Text']);
                                            if(is_numeric($valid))
                                            {
                                                $data_tmp .= ',,';
                                                $count_key++;
                                            }
                                        }
                                    }
                                }
                                $count++;
                            }
                            break;
                        }
                    }
                    
                    for($i = 0; $i < $inicio; $i++)
                    {
                        $data .= ',,';
                    }
                    $data .= $data_tmp;

                    for($i = ($fin + 1); $i < (count($sedungo_tiempo_goles) - $inicio); $i++)
                    {
                        $data .= ',,';
                    }
                }
                #endregion
                
                //REVISAR ORDEN DE EXTRACCION
                #region 2ª mitad - Número total exacto de goles
                if(isset($itm['Text']) && in_array(strtolower($itm['Text']), array(strtolower('2ª mitad - Número total exacto de goles'), strtolower('2 mitad - Número total exacto de goles')))){
                  // print_r('2ª mitad - Número total exacto de goles');print_r("<br>");
                    $data_a = '';
                    $data_b = '';
                    $count = 0;
                    for($x = ($index); $x < ($index+15); $x++){
                        if(strtolower($result['IdentityDocuments'][0]['Blocks'][$x]['Text']) == strtolower('Mitad con más goles')) break;
                        if(is_numeric($result['IdentityDocuments'][0]['Blocks'][$x]['Text']) && !is_numeric($result['IdentityDocuments'][0]['Blocks'][$x-1]['Text'])){



                            if ($count == 0) {
                                $data_a .= ',' . $result['IdentityDocuments'][0]['Blocks'][$x]['Text'];
                            }else{
                                $data_b .= ',' . $result['IdentityDocuments'][0]['Blocks'][$x]['Text'];
                                $count = -1;
                            }
                            $count++;


                            //$data .= ',' . $result['IdentityDocuments'][0]['Blocks'][$x]['Text'];


                            // print_r($result['IdentityDocuments'][0]['Blocks'][$x]['Text']);print_r("<br>");
                            //print_r($result['IdentityDocuments'][0]['Blocks'][$x+1]['Text']);print_r("<br>");
                        }
                    }
                    $data .= $data_a . $data_b;
                }
                #endregion

                #region Equipo local - Mitad con mayor n° de goles
                if(isset($itm['Text']) && in_array(strtolower($itm['Text']), array(strtolower('Equipo local - Mitad con mayor n° de goles'), strtolower('Equipo local Mitad con mayor n° de goles')))){
                    for($x = ($index); $x < ($index+5); $x++){
                        if(strtolower($result['IdentityDocuments'][0]['Blocks'][$x]['Text']) == strtolower('Equipo visitante - Mitad con mayor n° de goles')) break;
                        $var_exp = explode(' ', strtolower($result['IdentityDocuments'][0]['Blocks'][$x]['Text']));
                        if(count($var_exp) > 1 && is_numeric($var_exp[count($var_exp) - 1])){
                            $data .= ',' . $var_exp[count($var_exp) - 1];
                        }
                    }
                }
                #endregion

                #region Equipo visitante - Mitad con mayor n° de goles
                if(isset($itm['Text']) && in_array(strtolower($itm['Text']), array(strtolower('Equipo visitante - Mitad con mayor n° de goles'), strtolower('Equipo visitante Mitad con mayor n° de goles')))){
                    for($x = ($index); $x < ($index+5); $x++){
                        if(strtolower($result['IdentityDocuments'][0]['Blocks'][$x]['Text']) == strtolower('Portería a 0')) break;
                        $var_exp = explode(' ', strtolower($result['IdentityDocuments'][0]['Blocks'][$x]['Text']));
                        if(count($var_exp) > 1 && is_numeric($var_exp[count($var_exp) - 1])){
                            $data .= ',' . $var_exp[count($var_exp) - 1];
                        }
                    }
                }
                #endregion

                #region Portería a 0
                if(isset($itm['Text']) && (strtolower($itm['Text']) == strtolower('Portería a 0'))){
                  // print_r('Portería a 0');print_r("<br>");
                    if(!$porteria_cero)
                    {

                        $count_sect = 4;
                        for($x = ($index+2); $x < ($index+12); $x++){
                            if(strtolower($result['IdentityDocuments'][0]['Blocks'][$x]['Text']) == strtolower('Equipo - Total de goles')) break;
                            $var_exp = explode(' ', strtolower($result['IdentityDocuments'][0]['Blocks'][$x]['Text']));
                            if(count($var_exp) > 1 && is_numeric($var_exp[count($var_exp) - 1])){
                                if($count_sect==0) break;
                                $data .= ',' . $var_exp[count($var_exp) - 1];
                                $count_sect--;
                                $porteria_cero = true;
                            }
                        }
                        for($fal = 0;$fal < $count_sect; $fal++) $data .= ',FALTA';
                    }
                }
                #endregion

                //REVISAR ORDEN DE EXTRACCION Y CAMPOS VACIOS
                #region Equipo - Total de goles
                
                $equipo_total_goles_l = array(
                    '0.5',
                    '1.5',
                    '2.5',
                    '3.5',
                    '4.5',
                    '5.5',
                    '6.5',
                    '7.5',
                    '8.5',
                    '9.5',
                );
                $equipo_total_goles_v = array(
                    '0.5',
                    '1.5',
                    '2.5',
                    '3.5',
                    '4.5',
                    '5.5',
                    '6.5',
                    '7.5',
                );

                if(isset($itm['Text']) && in_array(strtolower($itm['Text']), array(strtolower('Equipo - Total de goles'), strtolower('Equipo Total de goles')))){
                    $data_one = array();
                    $data_two = array();
                    $last_index = 0;
                    foreach($equipo_total_goles_l as $indx => $obj_one)
                    {
                        $found = false;
                        for($x = ($index); $x < (count($result['IdentityDocuments'][0]['Blocks'])); $x++){
                            $var_exp = explode(' ', strtolower($result['IdentityDocuments'][0]['Blocks'][$x]['Text']));
                            if(!(($x - 2) == $last_index) &&     (count($var_exp) > 2 && strtolower($var_exp[0]) == strtolower('Más') && str_replace(',','.',$var_exp[count($var_exp) - 2]) == $obj_one))
                            {
                                $last_index = $x;
                                $found = true;

                                $data_one[] = str_replace(',','.',$var_exp[count($var_exp) - 1]);

                                $var_exp = explode(' ', strtolower($result['IdentityDocuments'][0]['Blocks'][$x+1]['Text']));
                                $data_one[] = str_replace(',','.',$var_exp[count($var_exp) - 1]);

                                
                                $var_exp = explode(' ', strtolower($result['IdentityDocuments'][0]['Blocks'][$x+2]['Text']));
                                if(isset($equipo_total_goles_v[$indx]) && strtolower($var_exp[0]) == strtolower('Más') && str_replace(',','.',$var_exp[count($var_exp) - 2]) == $obj_one)
                                {
                                    $data_two[] = str_replace(',','.',$var_exp[count($var_exp) - 1]);

                                    $var_exp = explode(' ', strtolower($result['IdentityDocuments'][0]['Blocks'][$x+3]['Text']));
                                    $data_two[] = str_replace(',','.',$var_exp[count($var_exp) - 1]);
                                }elseif(str_contains(str_replace(' ', '', strtolower($result['IdentityDocuments'][0]['Blocks'][$x+2]['Text'])), 'másde'. $obj_one))
                                {
                                    $data_two[] = str_replace('másde'. $obj_one, '', str_replace(' ', '', strtolower($result['IdentityDocuments'][0]['Blocks'][$x+2]['Text'])));
                                    $data_two[] = str_replace('menosde'. $obj_one, '', str_replace(' ', '', strtolower($result['IdentityDocuments'][0]['Blocks'][$x+3]['Text'])));
                                
                                }elseif(isset($equipo_total_goles_v[$indx])){
                                    $data_two[] = '';
                                    $data_two[] = '';
                                }
                                break;
                            }elseif(!(($x - 2) == $last_index) && str_contains(str_replace(' ', '', strtolower($result['IdentityDocuments'][0]['Blocks'][$x]['Text'])), 'másde'. $obj_one))
                            {
                                $last_index = $x;

                                $found = true;
                                $data_one[] = str_replace('másde'. $obj_one, '', str_replace(' ', '', strtolower($result['IdentityDocuments'][0]['Blocks'][$x]['Text'])));
                                $data_one[] = str_replace('menosde'. $obj_one, '', str_replace(' ', '', strtolower($result['IdentityDocuments'][0]['Blocks'][$x+1]['Text'])));

                                if(isset($equipo_total_goles_v[$indx]) && str_contains(str_replace(' ', '', strtolower($result['IdentityDocuments'][0]['Blocks'][$x+2]['Text'])), 'másde'. $obj_one))
                                {
                                    $data_two[] = str_replace('másde'. $obj_one, '', str_replace(' ', '', strtolower($result['IdentityDocuments'][0]['Blocks'][$x+2]['Text'])));
                                    $data_two[] = str_replace('menosde'. $obj_one, '', str_replace(' ', '', strtolower($result['IdentityDocuments'][0]['Blocks'][$x+3]['Text'])));
                                }elseif(isset($equipo_total_goles_v[$indx])){
                                    $data_two[] = '';
                                    $data_two[] = '';
                                }
                            }
                        }
                        if(!$found)
                        {
                            $data_one[] = '';
                            $data_one[] = '';
                            if(isset($equipo_total_goles_v[$indx])){
                                $data_two[] = '';
                                $data_two[] = '';
                            }
                        }
                    }
                    $data .= ','. str_replace('$','', implode(',', $data_one));
                    $data .= ','. str_replace('$','', implode(',', $data_two));
                }
                #endregion

                #region Equipo local - Número exacto de goles
                if(isset($itm['Text']) && in_array(strtolower($itm['Text']), array(strtolower('Equipo local - Número exacto de goles'), strtolower('Equipo local Número exacto de goles')))){
                    $contador_faltantes = 4;
                    // print_r('Equipo local - Número exacto de goles');print_r("<br>");
                    for($x = ($index); $x < (count($result['IdentityDocuments'][0]['Blocks'])); $x++){
                        $var_exp = explode(' ', strtolower($result['IdentityDocuments'][0]['Blocks'][$x]['Text']));
                        if(count($var_exp) > 1 && is_numeric($var_exp[count($var_exp) - 1])){
                            if($contador_faltantes == 0) break;
                            $data .= ',' . $var_exp[count($var_exp) - 1];
                            $contador_faltantes--;
                            
                        }
                    }
                    for($fal = 0;$fal < $contador_faltantes; $fal++) $data .= ',FALTA';
                }
                #endregion

                #region Equipo visitante - Número exacto de goles
                if(isset($itm['Text']) && in_array(strtolower($itm['Text']), array(strtolower('Equipo visitante - Número exacto de goles'), strtolower('Equipo visitante Número exacto de goles')))){
                    $contador_faltantes = 4;
                    // print_r('Equipo visitante - Número exacto de goles');print_r("<br>");
                    for($x = ($index); $x < (count($result['IdentityDocuments'][0]['Blocks'])); $x++){
                        $var_exp = explode(' ', strtolower($result['IdentityDocuments'][0]['Blocks'][$x]['Text']));
                        if(count($var_exp) > 1 && is_numeric($var_exp[count($var_exp) - 1])){
                            if($contador_faltantes == 0) break;
                            $data .= ',' . $var_exp[count($var_exp) - 1];
                            $contador_faltantes--;
                        }
                    }
                    for($fal = 0;$fal < $contador_faltantes; $fal++) $data .= ',FALTA';
                }
                #endregion
  
                #region Margen de victoria
                if(isset($itm['Text']) && (strtolower($itm['Text']) == strtolower('Margen de victoria'))){
                    $contador_faltantes = 10;
                    $count = 0;
                    for($y = ($index+3); $y <= (count($result['IdentityDocuments'][0]['Blocks'])); $y++){
                        if($contador_faltantes == 0) break;
                        if(strtolower($result['IdentityDocuments'][0]['Blocks'][$y]['Text']) == strtolower('Empate con goles')) {
                            $data .= ',' . $result['IdentityDocuments'][0]['Blocks'][$y+1]['Text'];
                            $contador_faltantes--;
                        }else if(strtolower($result['IdentityDocuments'][0]['Blocks'][$y]['Text']) == strtolower('Sin gol')) {
                            $data .= ',' . $result['IdentityDocuments'][0]['Blocks'][$y+1]['Text'];
                            $contador_faltantes--;
                            break;
                        }else{
                            if($count > 0)
                            {
                                $data .= ',' . $result['IdentityDocuments'][0]['Blocks'][$y]['Text'];
                                $contador_faltantes--;
                                if($count == 2) $count = -1;
                            }
                            $count++;
                        }
                    }
                    for($fal = 0;$fal < $contador_faltantes; $fal++) $data .= ',FALTA';
                }
                #endregion

                //REVISAR AGREGAR EL PENULTIMO DATO
                #region Minuto del 1° gol del equipo
                if(isset($itm['Text']) && (strtolower($itm['Text']) == strtolower('Minuto del 1° gol del equipo'))){
                    $gol_del_equipo = true;
                    $count_sect = 4;
                    for($x = ($index); $x < (count($result['IdentityDocuments'][0]['Blocks'])); $x++){
                        
                        $var_exp = explode(' ', strtolower($result['IdentityDocuments'][0]['Blocks'][$x]['Text']));
                        if(count($var_exp) > 1 && is_numeric(str_replace(',','.', $var_exp[count($var_exp) - 1]))){
                        
                            if($count_sect==0) break;
                            $data .= ',' . str_replace(',','.', $var_exp[count($var_exp) - 2]);
                            $data .= ',' . str_replace(',','.', $var_exp[count($var_exp) - 1]);
                            
                            $count_sect--;
                        }
                    }
                }
                #endregion

                #region Goles - N° par O impar || Goles - No par o impar
                if(isset($itm['Text']) && (strtolower($itm['Text']) == strtolower('Goles - N° par O impar') || strtolower($itm['Text']) == strtolower('Goles - No par o impar'))){
                  
                    if(!$gol_del_equipo){
                        $gol_del_equipo = true;
                        for($fal = 0;$fal < 8; $fal++) $data .= ',FALTA';
                    }

                    $count_sect = 0;
                    //$data .= '[';
                    for($x = ($index); $x < (count($result['IdentityDocuments'][0]['Blocks'])); $x++){
                        if(strtolower($result['IdentityDocuments'][0]['Blocks'][$x]['Text']) == strtolower('Equipo local - Goles - Impar/par')) break;
                        $var_exp = explode(' ', strtolower($result['IdentityDocuments'][0]['Blocks'][$x]['Text']));
                        if(count($var_exp) > 1 && is_numeric($var_exp[count($var_exp) - 1])){
                            if($count_sect >= 2) break;
                            $data .= ',' . $var_exp[count($var_exp) - 1];
                            // print_r($var_exp[count($var_exp) - 1]);print_r("<br>");
                            $count_sect++;
                        }
                    }
                    //$data .= ']';
                }
                #endregion

                #region Equipo local - Goles - Impar/par
                if(isset($itm['Text']) && (strtolower($itm['Text']) == strtolower('Equipo local - Goles - Impar/par'))){
                    //$data .= '[';
                    if(!$lgoles_impar_par)
                    {
                        $lgoles_impar_par = true;    
                        $count_sect = 0;
                        // print_r('Equipo local - Goles - Impar/par');print_r("<br>");
                        for($x = ($index); $x < (count($result['IdentityDocuments'][0]['Blocks'])); $x++){
                            if(strtolower($result['IdentityDocuments'][0]['Blocks'][$x]['Text']) == strtolower('Equipo visitante - Goles - Impar/par')) break;
                            $var_exp = explode(' ', strtolower($result['IdentityDocuments'][0]['Blocks'][$x]['Text']));
                            if(count($var_exp) > 1 && is_numeric($var_exp[count($var_exp) - 1])){
                                if($count_sect >= 2) break;
                                $data .= ',' . $var_exp[count($var_exp) - 1];
                                $count_sect++;
                                // print_r($var_exp[count($var_exp) - 1]);print_r("<br>");
                            }
                        }
                    }
                    //$data .= ']';
                }
                #endregion

                #region Equipo visitante - Goles - Impar/par 
                if(isset($itm['Text']) && (strtolower($itm['Text']) == strtolower('Equipo visitante - Goles - Impar/par'))){
                    //$data .= '[';
                    if(!$vgoles_impar_par)
                    {
                        $vgoles_impar_par = true;
                        $count_sect = 0;
                        // print_r('Equipo visitante - Goles - Impar/par');print_r("<br>");
                        for($x = ($index); $x < (count($result['IdentityDocuments'][0]['Blocks'])); $x++){
                            if(strtolower($result['IdentityDocuments'][0]['Blocks'][$x]['Text']) == strtolower('1ª mitad - Goles - Impar/Par')) break;
                            $var_exp = explode(' ', strtolower($result['IdentityDocuments'][0]['Blocks'][$x]['Text']));
                            if(count($var_exp) > 1 && is_numeric($var_exp[count($var_exp) - 1])){

                                if($count_sect >= 2) break;

                                $data .= ',' . $var_exp[count($var_exp) - 1];
                                // print_r($var_exp[count($var_exp) - 1]);print_r("<br>");

                                $count_sect++;
                            }
                        }
                    }
                    //$data .= ']';
                }
                #endregion

                #region 1ª mitad - Goles - Impar/Par
                if(isset($itm['Text']) && (strtolower($itm['Text']) == strtolower('1ª mitad - Goles - Impar/Par'))){
                    $contador_faltantes = 2;
                    if(!$pmitad_goles_impar_par)
                    {
                        $pmitad_goles_impar_par = true;    
                        for($x = ($index); $x < (count($result['IdentityDocuments'][0]['Blocks'])); $x++){
                            $var_exp = explode(' ', strtolower($result['IdentityDocuments'][0]['Blocks'][$x]['Text']));
                            if(count($var_exp) > 1 && is_numeric($var_exp[count($var_exp) - 1])){
                            if($contador_faltantes == 0) break;
                                $data .= ',' . $var_exp[count($var_exp) - 1];
                                $contador_faltantes--;
                            }
                        }
                    }
                }
                #endregion

                #region Último equipo en anotar
                if(isset($itm['Text']) && (strtolower($itm['Text']) == strtolower('Último equipo en anotar'))){
                    //$data .= '[';
                    if(!$ultimo_equipo_anotar)
                    {
                        $ultimo_equipo_anotar = true;    
                        $count_sect = 0;
                        // print_r('Último equipo en anotar');print_r("<br>");
                        for($x = ($index); $x < (count($result['IdentityDocuments'][0]['Blocks'])); $x++){
                            //if(strtolower($result['IdentityDocuments'][0]['Blocks'][$x]['Text']) == strtolower('Margen de victoria')) break;
                            $var_exp = explode(' ', strtolower($result['IdentityDocuments'][0]['Blocks'][$x]['Text']));
                            if(count($var_exp) > 1 && is_numeric($var_exp[count($var_exp) - 1])){
                                if($count_sect >= 3) break;
                                $data .= ',' . $var_exp[count($var_exp) - 1];
                                // print_r($var_exp[count($var_exp) - 1]);print_r("<br>");

                                $count_sect++;
                            }
                        }
                    }
                    //$data .= ']';
                }
                #endregion


            }
            // }catch(\Exception $e){
            // print_r($path_dni);
            // print_r($e->getMessage());
            // exit();
            // }
        }

    }
}
