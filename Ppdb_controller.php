<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\User;
use App\Models\Profile_sekolah;
use App\Models\Limit_peserta;
use App\Models\M_program;
use App\Models\M_asal_sekolah;
use App\Models\Jenis_kelamin;
use App\Models\Jadwal;

class Ppdb_controller extends Controller
{
    public function index(){
        $title = 'Daftar Akun PPDB Online';
        $profile = Profile_sekolah::get();
        $program = M_program::get();
        $asal_sekolah = M_asal_sekolah::get();
        $jenis_kelamin = Jenis_kelamin::get();

        $tahun_now = date('Y');
        $limit = Limit_peserta::where('tahun',$tahun_now)->first();

        if($limit){
            $batas = $limit->limit;
            $hitung = User::whereYear('tanggal_buat',date('Y'))->whereNull('role')->count();
            // dd($batas);
            if($hitung >= $batas){
                $cek = 'kosong';
            }else{
                $cek = 'ada';
            }
        }

        return view('ppdb.index',compact('title','cek','profile','program','asal_sekolah','jenis_kelamin'));
    }

    public function store(Request $request){
        $this->validate($request,[
            'nama' => 'required|min:5',
            'nisn' => 'required|unique:users,nisn',
            'program_id' => 'required',
            'asal_sekolah_id' => 'required',
            'jenis_kelamin_id' => 'required',
            'email' => 'required|email|unique:users',
            'photo' => 'file|mimes:jpeg,jpg,png|max:2048',
            'password' => 'required|min:6',
            'confirm_password' => 'required|same:password'
        ]);

        $data['name'] = $request->nama;
        $data['nisn'] = $request->nisn;
        $data['program_id'] = $request->program_id;
        $data['asal_sekolah_id'] = $request->asal_sekolah_id;
        $data['jenis_kelamin_id'] = $request->jenis_kelamin_id;
        $data['email'] = $request->email;
        $data['password'] = bcrypt($request->password);

        $data['tanggal_buat'] = date('Y-m-d H:i:s');

        // Format ID registrasi SD/MI-PA(Putra)/PI(Putri)-Program-ID(user)
        // sd-pa-tahfidz
        if(($request->asal_sekolah_id == 1) && ($request->jenis_kelamin_id == 1) && ($request->program_id == 1)){
            $data['id_registrasi'] = 'SD-PA-Tahfidz-'.date('Y');
        }
        // sd-pi-tahfidz
        elseif(($request->asal_sekolah_id == 1) && ($request->jenis_kelamin_id == 2) && ($request->program_id == 1)){
            $data['id_registrasi'] = 'SD-PI-Tahfidz-'.date('Y');
        }
        // mi-pa-tahfidz
        elseif(($request->asal_sekolah_id == 2) && ($request->jenis_kelamin_id == 1) && ($request->program_id == 1)){
            $data['id_registrasi'] = 'MI-PA-Tahfidz-'.date('Y');
        }
        // mi-pi-tahfidz
        elseif(($request->asal_sekolah_id == 2) && ($request->jenis_kelamin_id == 2) && ($request->program_id == 1)){
            $data['id_registrasi'] = 'MI-PI-Tahfidz-'.date('Y');
        }
        // sd-pa-sains-riset
        elseif(($request->asal_sekolah_id == 1) && ($request->jenis_kelamin_id == 1) && ($request->program_id == 2)){
            $data['id_registrasi'] = 'SD-PA-Sains_Riset-'.date('Y');
        }
        // sd-pi-sains-riset
        elseif(($request->asal_sekolah_id == 1) && ($request->jenis_kelamin_id == 2) && ($request->program_id == 2)){
            $data['id_registrasi'] = 'SD-PI-Sains_Riset-'.date('Y');
        }
        // mi-pa-sains-riset
        elseif(($request->asal_sekolah_id == 2) && ($request->jenis_kelamin_id == 1) && ($request->program_id == 2)){
            $data['id_registrasi'] = 'MI-PA-Sains_Riset-'.date('Y');
        }
        // mi-pi-sains-riset
        elseif(($request->asal_sekolah_id == 2) && ($request->jenis_kelamin_id == 2) && ($request->program_id == 2)){
            $data['id_registrasi'] = 'MI-PI-Sains_Riset-'.date('Y');
        }
        // sd-pa-utama
        elseif(($request->asal_sekolah_id == 1) && ($request->jenis_kelamin_id == 1) && ($request->program_id == 3)){
            $data['id_registrasi'] = 'SD-PA-Utama-'.date('Y');
        }
        // sd-pi-utama
        elseif(($request->asal_sekolah_id == 1) && ($request->jenis_kelamin_id == 2) && ($request->program_id == 3)){
            $data['id_registrasi'] = 'SD-PI-Utama-'.date('Y');
        }
        // mi-pa-utama
        elseif(($request->asal_sekolah_id == 2) && ($request->jenis_kelamin_id == 1) && ($request->program_id == 3)){
            $data['id_registrasi'] = 'MI-PA-Utama-'.date('Y');
        }
        // mi-pi-utama
        elseif(($request->asal_sekolah_id == 2) && ($request->jenis_kelamin_id == 2) && ($request->program_id == 3)){
            $data['id_registrasi'] = 'MI-PI-Utama-'.date('Y');
        }

        $file = $request->file('photo');
        if($file){
            $nama_file = rand().'-'. $file->getClientOriginalName();
            $file->move('uploads',$nama_file);
            $data['photo'] = 'uploads/' .$nama_file;
        }

        User::insert($data);

        \Session::flash('berhasil','Terima kasih, akun berhasil dibuat');

        return redirect('login');
    }

    public function jadwal_offline(){
        $title = 'Pendaftaran ditutup';
        $profile = Profile_sekolah::get();
        $program = M_program::get();
        $asal_sekolah = M_asal_sekolah::get();
        $jenis_kelamin = Jenis_kelamin::get();

        $tahun_now = date('Y');
        $limit = Limit_peserta::where('tahun',$tahun_now)->first();

        if($limit){
            $batas = $limit->limit;
            $hitung = User::whereYear('tanggal_buat',date('Y'))->whereNull('role')->count();
            // dd($batas);
            if($hitung >= $batas){
                $cek = 'kosong';
            }else{
                $cek = 'ada';
            }
        }

        return view('ppdb.jadwal_off',compact('title','cek','profile','program','asal_sekolah','jenis_kelamin'));
    }
}
