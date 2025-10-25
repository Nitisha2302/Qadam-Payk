<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\UserLang;

class ChatController extends Controller
{
    // Start or get a conversation
    public function start(Request $request) {
        $user = Auth::guard('api')->user();
        if (!$user) return response()->json(['status' => false,'message' => __('messages.start.user_not_authenticated')], 401);

        
       // 🔹 Detect user's preferred language from UserLang table
        $userLang = UserLang::where('user_id', $user->id)
            ->where('device_id', $user->device_id)
            ->where('device_type', $user->device_type)
            ->first();

        $lang = $userLang->language ?? 'ru'; // fallback to Russian
        app()->setLocale($lang);

        $validator = Validator::make($request->all(), [
            'other_user_id' => 'required|exists:users,id|not_in:' . $user->id,
        ], [
             'other_user_id.required' => __('messages.start.validation.other_user_id_required'),
            'other_user_id.exists'   => __('messages.start.validation.other_user_id_not_exist'),
            'other_user_id.not_in'   => __('messages.start.validation.other_user_id_not_in'),
        ]);

        if ($validator->fails()) return response()->json(['status' => false, 'message' => $validator->errors()->first()], 201);

        $conversation = Conversation::between($user->id, $request->other_user_id);

        return response()->json([
            'status'=>true,
            'message'      => __('messages.start.success'),
            'conversation'=>$conversation
        ],200);
    }

    // new with user details 

    public function allConversation() {
        $user = Auth::guard('api')->user();
        if (!$user) return response()->json(['status' => false,'message' =>__('messages.allConversation.user_not_authenticated')], 401);
        // 🔹 Detect user's preferred language from UserLang table
        $userLang = UserLang::where('user_id', $user->id)
            ->where('device_id', $user->device_id)
            ->where('device_type', $user->device_type)
            ->first();

        $lang = $userLang->language ?? 'ru'; // fallback to Russian
        app()->setLocale($lang);

        $meId = $user->id;
        $conversations = Conversation::where('user_one_id', $meId)
            ->orWhere('user_two_id', $meId)
            ->with(['messages.sender', 'userOne', 'userTwo']) // eager load users
            ->orderByDesc('last_message_at')
            ->get();

        $result = $conversations->map(function($c) use ($meId){
            $otherId = $c->otherUserId($meId);
            $otherUser = $c->otherUser($meId); // full user model
            $lastMsg = $c->messages->last();
            return [
            'conversation_id'   => $c->id,
            'other_user_id'     => $otherUser->id ?? null,
            'other_user_name'   => $otherUser->name ?? null,
            'other_user_phone'  => $otherUser->phone ?? null,
            'other_user_image'  => $otherUser->image,
            'last_message'      => $lastMsg ? $lastMsg->message : null,
            'last_message_time' => $lastMsg ? $lastMsg->created_at->toDateTimeString() : null,
            'unread_count'      => $c->messages->whereNull('read_at')->where('sender_id','!=',$meId)->count()
        ];
        });

        return response()->json(['status'=>true,'message'=>__('messages.allConversation.success'),'conversations'=>$result],200);
    }

    // Get messages in a conversation
    public function allMessages(Request $request) {
        $user = Auth::guard('api')->user();
        if (!$user) return response()->json(['status' => false,'message' => __('messages.allMessages.user_not_authenticated')], 401);

        // 🔹 Detect user's preferred language from UserLang table
        $userLang = UserLang::where('user_id', $user->id)
            ->where('device_id', $user->device_id)
            ->where('device_type', $user->device_type)
            ->first();

        $lang = $userLang->language ?? 'ru'; // fallback to Russian
        app()->setLocale($lang);

          $validator = Validator::make($request->all(), [
            'conversation_id' => 'required|exists:conversations,id',
        ], [
            'conversation_id.required' => __('messages.allMessages.validation.conversation_required'),
           'conversation_id.exists'   => __('messages.allMessages.validation.conversation_not_exist'),
        ]);

        if($validator->fails())
            return response()->json(['status'=>false,'errors'=>$validator->errors()->first()],201);

        $conversation = Conversation::find($request->conversation_id);

        if(!in_array($user->id, [$conversation->user_one_id,$conversation->user_two_id])) {
            return response()->json(['status'=>false,'message'=> __('messages.allMessages.not_participant')],201);
        }

        $messages = $conversation->messages()->with('sender')->get()->map(function($m) use ($user){
               $sender = $m->sender;
            return [
                'id'=>$m->id,
                'sender_id'=>$m->sender_id,
                 'is_me'        => $m->sender_id == $user->id,// ✅ true if current user sent it
                 'sender_name' => $sender->name ?? null,
                'sender_phone'=> $sender->phone ?? null,
                'sender_image'=>  $sender->image,
                'message'=>$m->message,
                'type'=>$m->type,
                'meta'=>$m->meta,
                 'send_at'=>$m->send_at ? $m->send_at->toDateTimeString() : null,
                'read_at'=>$m->read_at ? $m->read_at->toDateTimeString() : null,
                'created_at'=>$m->created_at->toDateTimeString(),
            ];
        });

        return response()->json(['status'=>true,'message'  => __('messages.allMessages.success'),'messages'=>$messages],200);
    }

    // Send a message
    // public function send(Request $request) {
    //     $user = Auth::guard('api')->user();
    //     if (!$user) return response()->json(['status' => false,'message' => 'User not authenticated'], 401);

    //     $validator = Validator::make($request->all(), [
    //         'conversation_id' => 'nullable|exists:conversations,id',
    //         'other_user_id'   => 'nullable|exists:users,id',
    //         'message'         => 'required|string|max:5000',
    //         'type'            => 'nullable|in:text,image,file,system'
    //     ], [
    //         'conversation_id.exists' => 'Conversation not found.',
    //         'other_user_id.exists'   => 'User does not exist.',
    //         'message.required'       => 'Message cannot be empty.',
    //         'message.string'         => 'Message must be text.',
    //         'message.max'            => 'Message is too long (max 5000 chars).',
    //         'type.in'                => 'Invalid message type.',
    //     ]);

    //     if($validator->fails())
    //         return response()->json(['status'=>false,'errors'=>$validator->errors()],201);

    //     if($request->conversation_id) {
    //         $conversation = Conversation::find($request->conversation_id);
    //     } else {
    //         $conversation = Conversation::between($user->id, $request->other_user_id);
    //     }

    //     if(!in_array($user->id, [$conversation->user_one_id,$conversation->user_two_id])) {
    //         return response()->json(['status'=>false,'message'=>'You are not a participant in this conversation'],201);
    //     }

    //     $message = Message::create([
    //         'conversation_id' => $conversation->id,
    //         'sender_id'       => $user->id,
    //         'message'         => $request->message,
    //         'type'            => $request->type ?? 'text',
    //         'send_at'         => now()  // ✅ Save send time
    //     ]);

    //     $conversation->update([
    //         'last_message_id'      => $message->id,
    //         'last_message_preview' => substr($message->message,0,200),
    //         'last_message_at'      => now()
    //     ]);

    //     return response()->json([
    //         'status'       => true,
    //         'message'      => 'Message sent successfully',
    //         'message_data' => [
    //             'id'              => $message->id,
    //             'conversation_id' => $conversation->id,
    //             'sender_id'       => $message->sender_id,
    //             'message'         => $message->message,
    //             'type'            => $message->type,
    //             'meta'            => $message->meta,
    //             'send_at'         => $message->send_at ? $message->send_at->toDateTimeString() : null,
    //             'read_at'         => $message->read_at ? $message->read_at->toDateTimeString() : null,
    //             'created_at'      => $message->created_at->toDateTimeString()
    //         ]
    //     ],200);
    // }

    // with notifications 

    public function send(Request $request) {
        $user = Auth::guard('api')->user();
        if (!$user) return response()->json(['status' => false,'message' => __('messages.send.user_not_authenticated')], 401);

        // 🔹 Detect user's preferred language from UserLang table
        $userLang = UserLang::where('user_id', $user->id)
            ->where('device_id', $user->device_id)
            ->where('device_type', $user->device_type)
            ->first();

        $lang = $userLang->language ?? 'ru'; // fallback to Russian
        app()->setLocale($lang);

        $validator = Validator::make($request->all(), [
            'conversation_id' => 'nullable|exists:conversations,id',
            'other_user_id'   => 'nullable|exists:users,id',
            'message'         => 'required|string|max:5000',
            'type'            => 'nullable|in:text,image,file,system'
        ], [
            'conversation_id.exists' => __('messages.send.validation.conversation_not_exist'),
            'other_user_id.exists'   => __('messages.send.validation.user_not_exist'),
            'message.required'       => __('messages.send.validation.message_required'),
            'message.string'         => __('messages.send.validation.message_string'),
            'message.max'            => __('messages.send.validation.message_max'),
            'type.in'                => __('messages.send.validation.type_invalid'),
        ]);

        if($validator->fails())
            return response()->json(['status'=>false,'errors'=>$validator->errors()->first()],201);

        if($request->conversation_id) {
            $conversation = Conversation::find($request->conversation_id);
        } else {
            $conversation = Conversation::between($user->id, $request->other_user_id);
        }

        if(!in_array($user->id, [$conversation->user_one_id,$conversation->user_two_id])) {
            return response()->json(['status'=>false,'message'=> __('messages.send.not_participant')],201);
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

        // ✅ Identify receiver
        $receiverId = $conversation->user_one_id == $user->id
            ? $conversation->user_two_id
            : $conversation->user_one_id;

        $receiver = \App\Models\User::find($receiverId);

        // ✅ Send FCM Notification
        // if ($receiver && $receiver->device_token) {
        //     $tokens = [
        //         [
        //             'device_token' => $receiver->device_token,
        //             'device_type'  => $receiver->device_type ?? 'android',
        //             'user_id'      => $receiver->id,
        //         ]
        //     ];

        //     // $notificationData = [
        //     //     'notification_type' => 2, // custom type for chat message
        //     //     'title' => '💬 New Message',
        //     //     'body'  => "{$user->name} sent you a message: {$request->message}",
        //     // ];

        //     $notificationData = [
        //         'notification_type' => 2,
        //         'title' => __('messages.send.notification.title'),
        //         'body'  => __('messages.send.notification.body', [
        //             'sender'  => $user->name,
        //             'message' => $request->message,
        //         ]),
        //     ];

        //     // Import your FCM service class if not already
        //     $fcmService = new \App\Services\FCMService();
        //     $fcmService->sendNotification($tokens, $notificationData);
        // }

        // ✅ Send Notification in Receiver's Language
    if ($receiver && $receiver->device_token) {
        // 🔹 Detect Receiver's preferred language
        $receiverLang = UserLang::where('user_id', $receiver->id)
            ->where('device_id', $receiver->device_id)
            ->where('device_type', $receiver->device_type)
            ->first();
            
        $lang = $receiverLang->language ?? 'ru'; // fallback to Russian
        app()->setLocale($lang);

        $tokens = [
            [
                'device_token' => $receiver->device_token,
                'device_type'  => $receiver->device_type ?? 'android',
                'user_id'      => $receiver->id,
            ]
        ];

        $notificationData = [
            'notification_type' => 2, // custom type for chat message
            'title' => __('messages.send.notification.title'),
            'body'  => __('messages.send.notification.body', [
                'sender'  => $user->name,
                'message' => $request->message,
            ]),
        ];

        $fcmService = new \App\Services\FCMService();
        $fcmService->sendNotification($tokens, $notificationData);
    }

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

            // Get IDs of unread messages before update
            $unreadMessageIds = Message::where('conversation_id', $conversation->id)
                ->where('sender_id', '!=', $user->id)
                ->whereNull('read_at')
                ->pluck('id')
                ->toArray();

            if (empty($unreadMessageIds)) {
                return response()->json([
                    'status' => false,
                    'message' => 'No unread messages to mark as read.'
                ], 200);
            }

            // Update them all
            Message::whereIn('id', $unreadMessageIds)->update(['read_at' => now()]);

            return response()->json([
                'status' => true,
                'message' => "Messages marked as read successfully",
                'marked_count' => count($unreadMessageIds),
                'marked_ids' => $unreadMessageIds
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'You must provide either a conversation_id or a message_id.'
        ], 400);
    }



}
