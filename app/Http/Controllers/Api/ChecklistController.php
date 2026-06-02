// ... [Substitua o método registrarAvaria] ...

    public function registrarAvaria(Request $request): JsonResponse
    {
        // MAX: 5242880 (5MB). Se for maior que isso, o Laravel rejeita gastando 0 bytes de memória de processamento.
        // Isso protege a HostGator de estourar a memória se a compressão front-end falhar.
        $request->validate([
            'pedido_id' => ['required', 'exists:pedidos,id'],
            'foto_base64' => ['required', 'string', 'starts_with:data:image/', 'max:5242880']
        ]);

        try {
            DB::beginTransaction();

            $imageParts = explode(";base64,", $request->foto_base64);
            $mimeTypeAux = explode("data:image/", $imageParts[0]);
            $mimeType = $mimeTypeAux[1] ?? '';
            
            $allowedExtensions = ['jpeg' => 'jpg', 'jpg' => 'jpg', 'png' => 'png', 'webp' => 'webp'];
            if (!array_key_exists($mimeType, $allowedExtensions)) {
                return response()->json(['status' => 'error', 'message' => 'Mime-type malicioso detectado.'], 422);
            }
            
            $imageExtension = $allowedExtensions[$mimeType];
            $imageBytes = base64_decode($imageParts[1]);

            if (str_contains($imageBytes, '<?php') || str_contains($imageBytes, '<script')) {
                return response()->json(['status' => 'error', 'message' => 'Payload malicioso barrado.'], 403);
            }

            $fileName = 'avarias/pedido_' . $request->pedido_id . '_' . bin2hex(random_bytes(8)) . '.' . $imageExtension;
            Storage::disk('public')->put($fileName, $imageBytes);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Evidência fotográfica persistida no disco.',
                'path' => $fileName
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Falha crítica de gravação.'], 500);
        }
    }