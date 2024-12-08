<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Database;

class ChatController extends Controller
{
    protected $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function index()
    {
        return view('chat');
    }

    public function getMessages()
    {
        try {
            // First get all messages
            $reference = $this->database->getReference('messages')->getValue();
            
            if (!$reference) {
                return response()->json([]);
            }

            // Sort messages by timestamp manually
            $messages = collect($reference)->sortBy('timestamp')->values();
            
            // Take last 100 messages
            $messages = $messages->take(-100);

            return response()->json($messages);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'text' => 'required|string|max:1000',
        ]);

        try {
            $user = auth()->user();
            $message = [
                'userId' => $user->id,
                'userName' => $user->name,
                'text' => $request->text,
                'timestamp' => time() * 1000,
            ];

            $this->database->getReference('messages')->push($message);

            return response()->json(['success' => true, 'message' => $message]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
