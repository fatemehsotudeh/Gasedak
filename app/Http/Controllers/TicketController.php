<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\TicketStatus;
use App\Models\UserAvatar;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

use App\Libraries;

class TicketController extends Controller
{
    //
    public function sendTicket(Request $request)
    {
        //decode bearer token
        $helper=new Libraries\Helper();
        $identifiedUser=$helper->decodeBearerToken($request->bearerToken());

        //posted params
        $ticketId = $request->ticketId;
        $title = $request->title;
        $message=$request->message;
        $attachment=$request->attachment;

        //if the user ticketStatus table is empty, we insert data in it
        if (TicketStatus::count()==0){
            $helper->initializeTicketStatusTable();
        }

        if (empty($message)){
            return response()->json(['message'=>'You must fill message field']);
        }

        //If the ID ticket is blank, a new ticket will be created
        if (empty($ticketId)){
            return $this->createNewTicket($title,$message,$attachment,$identifiedUser->id);
        }else{
            try {
                $ticket=Ticket::where('id',$ticketId);
                    if ($ticket->exists()){
                        if ($ticket->pluck('ticketStatusId')[0]!=3){
                            $ticketmessage=new TicketMessage();
                            if (empty($attachment)){
                                $ticketmessage->ticketId=$ticketId;
                                $ticketmessage->senderId=$identifiedUser->id;
                                $ticketmessage->message=$message;
                                $ticketmessage->save();
                                return response()->json(['message'=>'send ticket successfully']);
                            }
                            if (is_file($attachment)){
                                if(is_string($filePath=$this->checkAndReceiveFile($attachment))){
                                    $ticketmessage->ticketId=$ticketId;
                                    $ticketmessage->senderId=$identifiedUser->id;
                                    $ticketmessage->message=$message;
                                    $ticketmessage->filePath=$filePath;
                                    $ticketmessage->save();
                                    return response()->json(['message'=>'send ticket successfully']);
                                }else{
                                    return $this->checkAndReceiveFile($attachment);
                                }
                            }
                    }else{
                        return response()->json(['status'=>'error','message'=>'This ticket is closed']);
                    }
                }
            }catch (\Exception $e){
                return response()->json(['status'=>'error','message'=>$e->getMessage()],500);
            }
        }
    }

    public function createNewTicket($title,$message,$attachment,$userId){
        try {
            //save ticket
            $ticket=new Ticket();
            $ticket->userId=$userId;
            $ticket->ticketStatusId=1;
            $ticket->title=$title;

            $ticketmessage=new TicketMessage();

            if (empty($attachment)){
                $ticket->save();
                $ticketmessage->ticketId=$ticket->id;
                $ticketmessage->senderId=$userId;
                $ticketmessage->message=$message;
                $ticketmessage->save();
                return response()->json(['message'=>'send ticket successfully']);
            }
            if (is_file($attachment)){
                if(is_string($filePath=$this->checkAndReceiveFile($attachment))){
                    $ticket->save();
                    $ticketmessage->ticketId=$ticket->id;
                    $ticketmessage->senderId=$userId;
                    $ticketmessage->message=$message;
                    $ticketmessage->filePath=$filePath;
                    $ticketmessage->save();
                    return response()->json(['message'=>'send ticket successfully']);
                }else{
                    return $this->checkAndReceiveFile($attachment);
                }
            }
        }catch (\Exception $e){
            return response()->json(['status'=>'error','message'=>$e->getMessage()],500);
        }
    }

    public function checkAndReceiveFile($attachment)
    {
        $uploadFilePath=$attachment;
        $fileSize=$attachment->getSize();
        $fileOriginalName=$attachment->getClientOriginalName();

        $helper=new Libraries\Helper();
        $maxSize=$helper->maxFileSize();

        //check size of image
        if ($fileSize<=$maxSize){
            //check type of file
            if ($helper->isAllowedFileType($attachment->getMimeType())){
                $fileSavePath=$helper->fileSavePath($fileOriginalName);
                if(move_uploaded_file($uploadFilePath,$fileSavePath)){
                    return $fileSavePath;
                }
            }else{
                return response()->json(['status'=>'error','message'=>'The uploaded file is not a photo or zip']);
            }
        }else{
            return response()->json(['status'=>'error','message'=>'Photo size is larger than allowed']);
        }
    }

    public function ticketList(Request $request)
    {
        //decode bearer token
        $helper=new Libraries\Helper();
        $identifiedUser=$helper->decodeBearerToken($request->bearerToken());

        try {
              $ticketsList=Ticket::join('ticketmessages', 'tickets.id', '=', 'ticketId')
                 ->where('tickets.userId',$identifiedUser->id)
                 ->orderBy('ticketId')->get();

              return response()->json(['data'=>$ticketsList,'message'=>'the list of tickets was returned successfully'],200);

        }catch (\Exception $e){
            return response()->json(['status'=>'error','message'=>$e->getMessage()],500);
        }
    }
}
