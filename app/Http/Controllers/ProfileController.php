<?php
 
 namespace App\Http\Controllers;
 
 use Illuminate\Http\Request;
 use Illuminate\Support\Facades\Auth;
 use Illuminate\Support\Facades\Storage;
 use App\Models\User;
 
 
 class ProfileController extends Controller
 {
     public function index()
     {
         return view('profile.index', [
             'user' => Auth::user()
         ]);
     }
 
     public function update(Request $request)
     {
         $request->validate([
             'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // maksimal 2MB
         ]);
 
         /** @var \App\Models\User $user */
         $user = Auth::user();
 
         if ($request->hasFile('foto')) {
             // Hapus foto lama jika ada
             if ($user->foto && Storage::exists('public/foto/' . $user->foto)) {
                 Storage::delete('public/foto/' . $user->foto);
             }
 
             // Simpan foto baru
             $file = $request->file('foto');
             $namaFile = uniqid() . '.' . $file->getClientOriginalExtension();
             $file->storeAs('public/foto', $namaFile);
 
             // Update kolom foto pada user
             $user->foto = $namaFile;
             $user->save();
         }
 
         return redirect()->back()->with('success', 'Foto profil berhasil diperbarui!');
     }
 
     public function delete()
     {
         /** @var \App\Models\User $user */
         $user = auth()->user();
 
         // Hapus file foto jika ada
         if ($user->foto && Storage::exists('public/foto/' . $user->foto)) {
             Storage::delete('public/foto/' . $user->foto);
         }
         
         $user->foto = null;
         $user->save();
 
         return response()->json(['success' => true]);
     }
 
 }