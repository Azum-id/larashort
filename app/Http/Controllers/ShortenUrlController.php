<?php

namespace App\Http\Controllers;

use App\Models\ShortenUrl;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ShortenUrlController extends Controller
{
    public function index(Request $request)
    {
        try {
            // validate the request
            $validatedData = $request->validate([
                'url' => 'required|url'
            ]);

            // Dapatkan URL dari request setelah lolos validasi
            $url = $validatedData['url'];
            $uniqueCode = $this->generateUniqueTransactionCode(8);
            // Buat short url
            $shortenUrl = ShortenUrl::create([
                'url' => $url,
                'shorten_url' => $uniqueCode
            ]);

            // Kembalikan respon JSON
            return response()->json([
                'message' => 'Success create short url',
                'data' => $shortenUrl

            ]);
        } catch (ValidationException $e) {
            // Tangani kesalahan validasi dan kirimkan pesan kesalahan
            return response()->json([
                'error' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Tangani kesalahan lainnya
            return response()->json([
                'error' => 'Something went wrong.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function redirect($shorten_url)
    {
        try {
            // Dapatkan URL asli dari short url
            $shortenUrl = ShortenUrl::where('shorten_url', $shorten_url)->firstOrFail();

            // Redirect ke URL asli
            return redirect()->away($shortenUrl->url);
        } catch (\Exception $e) {
            // Tangani kesalahan lainnya
            return response()->json([
                'error' => 'Something went wrong.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function generateUniqueTransactionCode($length = 8)
    {
        $code = $this->generateRandomCode($length);


        while (ShortenUrl::where('shorten_url',  $code)->exists()) {
            $code = $this->generateRandomCode($length);
        }

        return $code;
    }


    private function generateRandomCode($length = 8)
    {
        // generate random string with uppercase and lowercase letters and digits
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = Str::length($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= Str::substr($characters, rand(0, $charactersLength - 1), 1);
        }
        return $randomString;
    }
}
