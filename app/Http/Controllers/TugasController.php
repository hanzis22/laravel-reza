<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tugas;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Smalot\PdfParser\Parser;
class TugasController extends Controller
{
    public function store(Request $request)
    {
       $file = $request->file('file');

$namaFile = time().'_'.$file->getClientOriginalName();

$file->storeAs(
    'tugas',
    $namaFile,
    'public'
);

Tugas::create([
    'user_id' => auth()->id(),
    'judul' => $request->judul,
    'file' => $namaFile,
    'minggu' => $request->minggu,
    'status' => 'Belum Diperiksa',
]);

return redirect('/peserta/upload')
       ->with('success', 'Tugas berhasil diupload');
        //dd($namaFile);
    }

    public function indexAdmin()
    {
        $tugas = Tugas::with('user')
            ->latest()
            ->get();

        return view('admin.tugas', compact('tugas'));
    }

    public function updateStatus(Request $request, $id)
{
    $tugas = Tugas::findOrFail($id);

    $tugas->update([
        'status' => $request->status,
        'komentar' => $request->komentar,
    ]);

    return redirect()
        ->back()
        ->with('success', 'Status tugas berhasil diperbarui.');
}

public function simpanKomentar(Request $request, $id)
{
    $request->validate([
        'komentar' => 'required|string|max:1000',
    ]);

    $tugas = Tugas::findOrFail($id);

    $tugas->update([
        'komentar' => $request->komentar,
    ]);

    return redirect()
        ->back()
        ->with('success', 'Komentar berhasil disimpan.');
}

public function download($id)
{
    $tugas = Tugas::findOrFail($id);

    return Storage::disk('public')
        ->download(
            'tugas/'.$tugas->file
        );
}
public function analisisAI($id)
{
    $tugas = Tugas::findOrFail($id);

    $filePath = storage_path(
        'app/public/tugas/' . $tugas->file
    );

    if (!file_exists($filePath)) {
        return back()->with(
            'error',
            'File tidak ditemukan.'
        );
    }

    try {

        $parser = new Parser();

        $pdf = $parser->parseFile($filePath);

        $text = substr(
            $pdf->getText(),
            0,
            3000
        );

        $prompt = "
Kamu adalah mentor magang IT.

Analisis laporan berikut dan berikan:

1. Ringkasan laporan
2. Kelebihan
3. Kekurangan
4. Saran perbaikan

Isi laporan:

$text
";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('GROQ_API_KEY'),
            'Content-Type' => 'application/json',
        ])->post(
            'https://api.groq.com/openai/v1/chat/completions',
            [
                'model' => 'llama-3.1-8b-instant',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.5
            ]
        );

        if (!$response->successful()) {

            return back()->with(
                'error',
                'Groq Error: ' . $response->body()
            );
        }

        $hasilAI =
            $response->json()['choices'][0]['message']['content']
            ?? 'Analisis AI gagal dibuat';

        $tugas->update([
            'ai_review' => $hasilAI
        ]);

        return back()->with(
            'success',
            'Analisis AI berhasil dibuat.'
        );

    } catch (\Exception $e) {

        return back()->with(
            'error',
            $e->getMessage()
        );
    }
}
    public function edit($id)
{
    $tugas = Tugas::where('user_id', auth()->id())
        ->findOrFail($id);

    return view('peserta.edit', compact('tugas'));
}

public function update(Request $request, $id)
{
    $tugas = Tugas::where('user_id', auth()->id())
        ->findOrFail($id);

    $request->validate([
        'judul' => 'required',
        'minggu' => 'required'
    ]);

    $tugas->update([
        'judul' => $request->judul,
        'minggu' => $request->minggu
    ]);

    return redirect()
        ->route('peserta.tugas')
        ->with('success', 'Tugas berhasil diperbarui');
}

public function destroy($id)
{
    $tugas = Tugas::where('user_id', auth()->id())
        ->findOrFail($id);

    if ($tugas->file && file_exists(public_path($tugas->file))) {
        unlink(public_path($tugas->file));
    }

    $tugas->delete();

    return back()->with(
        'success',
        'Tugas berhasil dihapus'
    );
}
}
