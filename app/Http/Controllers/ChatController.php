<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    // Start or get a conversation
    public function start(Request $request) {
        $user = Auth::guard('api')->user();
        if (!$user) return response()->json(['status' => false,'message' => 'User not authenticated'], 401);

        $validator = Validator::make($request->all(), [
            'other_user_id' => 'required|exists:users,id|not_in:' . $user->id,
        ], [
            'other_user_id.required' => 'Other user ID is required.',
            'other_user_id.exists'   => 'The user you are trying to chat with does not exist.',
            'other_user_id.not_in'   => 'You cannot start a chat with yourself.',
        ]);

        if ($validator->fails()) return response()->json(['status' => false, 'message' => $validator->errors()->first()], 201);

        $conversation = Conversation::between($user->id, $request->other_user_id);

        return response()->json([
            'status'=>true,
            'message'=>'Conversation started successfully',
            'conversation'=>$conversation
        ],200);
    }

    // List all conversations
    public function allConversation() {
        $user = Auth::guard('api')->user();
        if (!$user) return response()->json(['status' => false,'message' => 'User not authenticated'], 401);

        $meId = $user->id;
        $conversations = Conversation::where('user_one_id', $meId)
            ->orWhere('user_two_id', $meId)
            ->with(['messages.sender'])
            ->orderByDesc('last_message_at')
            ->get();

        $result = $conversations->map(function($c) use ($meId){
            $otherId = $c->otherUserId($meId);
            $lastMsg = $c->messages->last();
            return [
                'conversation_id' => $c->id,
                'other_user_id'   => $otherId,
                'last_message'    => $lastMsg ? $lastMsg->message : null,
                'last_message_time'=> $lastMsg ? $lastMsg->created_at->toDateTimeString() : null,
                'unread_count'    => $c->messages->whereNull('read_at')->where('sender_id','!=',$meId)->count()
            ];
        });

        return response()->json(['status'=>true,'message'=>'Conversations fetched successfully','conversations'=>$result],200);
    }

    // Get messages in a conversation
    public function allMessages(Request $request) {
        $user = Auth::guard('api')->user();
        if (!$user) return response()->json(['status' => false,'message' => 'User not authenticated'], 401);

          $validator = Validator::make($request->all(), [
            'conversation_id' => 'required|exists:conversations,id',
        ], [
            'conversation_id.required' => 'Conversation ID is required.',
            'conversation_id.exists'   => 'Conversation does not exist.'
        ]);

        if($validator->fails())
            return response()->json(['status'=>false,'errors'=>$validator->errors()],201);

        $conversation = Conversation::find($request->conversation_id);

        if(!in_array($user->id, [$conversation->user_one_id,$conversation->user_two_id])) {
            return response()->json(['status'=>false,'message'=>'You are not a participant in this conversation'],201);
        }

        $messages = $conversation->messages()->with('sender')->get()->map(function($m){
            return [
                'id'=>$m->id,
                'sender_id'=>$m->sender_id,
                'message'=>$m->message,
                'type'=>$m->type,
                'meta'=>$m->meta,
                 'send_at'=>$m->send_at ? $m->send_at->toDateTimeString() : null,
                'read_at'=>$m->read_at ? $m->read_at->toDateTimeString() : null,
                'created_at'=>$m->created_at->toDateTimeString(),
            ];
        });

        return response()->json(['status'=>true,'message'=>'Messages fetched successfully','messages'=>$messages],200);
    }

    // Send a message
    public function send(Request $request) {
        $user = Auth::guard('api')->user();
        if (!$user) return response()->json(['status' => false,'message' => 'User not authenticated'], 401);

        $validator = Validator::make($request->all(), [
            'conversation_id' => 'nullable|exists:conversations,id',
            'other_user_id'   => 'nullable|exists:users,id',
            'message'         => 'required|string|max:5000',
            'type'            => 'nullable|in:text,image,file,system'
        ], [
            'conversation_id.exists' => 'Conversation not found.',
            'other_user_id.exists'   => 'User does not exist.',
            'message.required'       => 'Message cannot be empty.',
            'message.string'         => 'Message must be text.',
            'message.max'            => 'Message is too long (max 5000 chars).',
            'type.in'                => 'Invalid message type.',
        ]);

        if($validator->fails())
            return response()->json(['status'=>false,'errors'=>$validator->errors()],201);

        if($request->conversation_id) {
            $conversation = Conversation::find($request->conversation_id);
        } else {
            $conversation = Conversation::between($user->id, $request->other_user_id);
        }

        if(!in_array($user->id, [$conversation->user_one_id,$conversation->user_two_id])) {
            return response()->json(['status'=>false,'message'=>'You are not a participant in this conversation'],201);
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $user->id,
            'message'         => $request->message,
            'type'            => $request->type ?? 'text',
            'send_at'         => now()  // ✅ Save send time
        ]);

        $conversation->update([
            'last_message_id'      => $message->id,
            'last_message_preview' => substr($message->message,0,200),
            'last_message_at'      => now()
        ]);

        return response()->json([
            'status'       => true,
            'message'      => 'Message sent successfully',
            'message_data' => [
                'id'              => $message->id,
                'conversation_id' => $conversation->id,
                'sender_id'       => $message->sender_id,
                'message'         => $message->message,
                'type'            => $message->type,
                'meta'            => $message->meta,
                'send_at'         => $message->send_at ? $message->send_at->toDateTimeString() : null,
                'read_at'         => $message->read_at ? $message->read_at->toDateTimeString() : null,
                'created_at'      => $message->created_at->toDateTimeString()
            ]
        ],200);
    }


    // Mark messages as read with conversation 
    // public function markRead(Request $request) {
    //     $user = Auth::guard('api')->user();
    //     if (!$user) return response()->json(['status' => false,'message' => 'User not authenticated'], 401);

    //     $validator = Validator::make($request->all(), [
    //         'conversation_id' => 'required|exists:conversations,id'
    //     ]);

    //     if ($validator->fails()) return response()->json(['status'=>false,'message'=>$validator->errors()->first()],201);

    //     $conversation = Conversation::find($request->conversation_id);

    //     if(!in_array($user->id, [$conversation->user_one_id,$conversation->user_two_id])) {
    //         return response()->json(['status'=>false,'message'=>'You are not a participant in this conversation'],201);
    //     }

    //     $count = Message::where('conversation_id', $conversation->id)
    //         ->where('sender_id','!=',$user->id)
    //         ->whereNull('read_at')
    //         ->update(['read_at'=>now()]);

    //     return response()->json(['status'=>true,'message'=>"Messages marked as read successfully",'marked_count'=>$count],200);
    // }

    // with both  
    public function markRead(Request $request) {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        // Validation
        $validator = Validator::make($request->all(), [
            'conversation_id' => 'nullable|exists:conversations,id',
            'message_id'      => 'nullable|exists:messages,id',
        ], [
            'conversation_id.exists' => 'The conversation you are trying to access does not exist.',
            'message_id.exists'      => 'The message you are trying to mark as read does not exist.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 201);
        }

        // Case 1: Mark a single message
        if ($request->message_id) {
            $message = Message::find($request->message_id);

            if (!$message) {
                return response()->json([
                    'status' => false,
                    'message' => 'Message not found.'
                ], 404);
            }

            if (!in_array($user->id, [$message->conversation->user_one_id, $message->conversation->user_two_id])) {
                return response()->json([
                    'status' => false,
                    'message' => 'You are not allowed to mark this message as read.'
                ], 201);
            }

            if ($message->sender_id == $user->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'You cannot mark your own message as read.'
                ], 201);
            }

            if ($message->read_at) {
                return response()->json([
                    'status' => false,
                    'message' => 'This message is already marked as read.'
                ], 201);
            }

            $message->update(['read_at' => now()]);

            return response()->json([
                'status' => true,
                'message' => 'Message marked as read successfully',
                'marked_message_id' => $message->id
            ], 200);
        }

        // Case 2: Mark all messages in a conversation
        if ($request->conversation_id) {
            $conversation = Conversation::find($request->conversation_id);

            if (!$conversation) {
                return response()->json([
                    'status' => false,
                    'message' => 'Conversation not found.'
                ], 404);
            }

            if (!in_array($user->id, [$conversation->user_one_id, $conversation->user_two_id])) {
                return response()->json([
                    'status' => false,
                    'message' => 'You are not a participant in this conversation.'
                ], 403);
            }

            $count = Message::where('conversation_id', $conversation->id)
                ->where('sender_id', '!=', $user->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            if ($count == 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'No unread messages to mark as read.'
                ], 200);
            }

            return response()->json([
                'status' => true,
                'message' => "Messages marked as read successfully",
                'marked_count' => $count
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'You must provide either a conversation_id or a message_id.'
        ], 400);
    }


}
