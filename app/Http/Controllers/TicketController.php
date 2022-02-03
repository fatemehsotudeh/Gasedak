<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\TicketStatus;
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
                        if ($ticket->pluck('ticketStatusId')[0]!=TicketStatus::where('name','closed')->pluck('id')[0]){
                            $ticketmessage=new TicketMessage();
                            if (empty($attachment)){
                                $ticketmessage->ticketId=$ticketId;
                                $ticketmessage->senderId=$identifiedUser->id;
                                $ticketmessage->message=$message;
                                $ticketmessage->save();
                                return response()->json(['message'=>'send ticket successfully'],200);
                            }
                            if (is_file($attachment)){
                                if(is_string($filePath=$this->checkAndReceiveFile($attachment))){
                                    $ticketmessage->ticketId=$ticketId;
                                    $ticketmessage->senderId=$identifiedUser->id;
                                    $ticketmessage->message=$message;
                                    $ticketmessage->filePath=$filePath;
                                    $ticketmessage->save();
                                    return response()->json(['message'=>'send ticket successfully'],200);
                                }else{
                                    return $this->checkAndReceiveFile($attachment);
                                }
                            }
                    }else{
                        return response()->json(['status'=>'error','message'=>'This ticket is closed'],400);
                    }
                }
            }catch (\Exception $e){
                return response()->json(['status'=>'error','message'=>$e->getMessage()],500);
            }
        }
    }

    public function createNewTicket($title,$message,$attachment,$userId)
    {
        try {
            //save ticket
            $ticket=new Ticket();
            $ticket->userId=$userId;
            $ticket->ticketStatusId=TicketStatus::where('name','waiting for answer')->pluck('id')[0];
            $ticket->title=$title;

            $ticketmessage=new TicketMessage();

            if (empty($attachment)){
                $ticket->save();
                $ticketmessage->ticketId=$ticket->id;
                $ticketmessage->senderId=$userId;
                $ticketmessage->message=$message;
                $ticketmessage->save();
                return response()->json(['message'=>'send ticket successfully'],200);
            }
            if (is_file($attachment)){
                if(is_string($filePath=$this->checkAndReceiveFile($attachment))){
                    $ticket->save();
                    $ticketmessage->ticketId=$ticket->id;
                    $ticketmessage->senderId=$userId;
                    $ticketmessage->message=$message;
                    $ticketmessage->filePath=$filePath;
                    $ticketmessage->save();
                    return response()->json(['message'=>'send ticket successfully'],200);
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
                return response()->json(['status'=>'error','message'=>'The uploaded file is not a photo or zip'],400);
            }
        }else{
            return response()->json(['status'=>'error','message'=>'Photo size is larger than allowed'],400);
        }
    }

    public function mainTicketList(Request $request)
    {
        //decode bearer token
        $helper=new Libraries\Helper();
        $identifiedUser=$helper->decodeBearerToken($request->bearerToken());

        //return main tickets
        try {
            $tickets=Ticket::where('tickets.userId',$identifiedUser->id);
            if ($tickets->exists()){
                $ticketsList=Ticket::join('ticketmessages', [['tickets.id', '=', 'ticketId'],['ticketmessages.created_at', '=', 'tickets.created_at']])
                    ->join('ticketsstatus', 'ticketsstatus.id', '=', 'tickets.ticketStatusId')
                    ->get();

                return response()->json(['data'=>$ticketsList,'message'=>'the list of tickets was returned successfully'],200);
            }else{
                return response()->json(['status'=>'error','message'=>"no tickets found for this user"],404);
            }
        }catch (\Exception $e){
            return response()->json(['status'=>'error','message'=>$e->getMessage()],500);
        }
    }

    public function subTicketList(Request $request)
    {
        //decode bearer token
        $helper=new Libraries\Helper();
        $identifiedUser=$helper->decodeBearerToken($request->bearerToken());

        $ticketId=$request->ticketId;

        if (empty($ticketId)){
            return response()->json(['message'=>'You must fill ticket id field']);
        }

        //return ticket messages
        try {
            $ticketsMessageList=TicketMessage::where('ticketId',$ticketId)->get();

            //Delete the original ticket message
            $ticketsMessageList->shift(1);

            //If the array was empty, there is no message for this ticket
            //else return messages successfully
            if (sizeof($ticketsMessageList) > 0){
                return response()->json(['data' =>$ticketsMessageList,'message'=>'the list of tickets was returned successfully'],200);
            }else{
                return response()->json(['status' =>'error','message'=>'no messages were found for the ticket'],404);
            }
        }catch (\Exception $e){
            return response()->json(['status'=>'error','message'=>$e->getMessage()],500);
        }
    }
}
