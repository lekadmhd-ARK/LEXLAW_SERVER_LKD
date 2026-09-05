<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Regulation;
use App\Models\RegulationContent;
class PdfController extends Controller
{
 public function upload(Request $request){$v=$request->validate(['pdf'=>'required|file|mimes:pdf|max:20480','regulation_id'=>'required|exists:regulations,id']);$path=$request->file('pdf')->store('regulations','public');return response()->json(['path'=>$path,'download_url'=>Storage::disk('public')->temporaryUrl($path,now()->addMinutes(15))]);}
 public function parseText(Request $request){$v=$request->validate(['regulation_id'=>'required|exists:regulations,id','text'=>'required|string']);$parts=preg_split('/(?=\bPasal\s+\d+[A-Za-z]?\b)/iu',$v['text'],-1,PREG_SPLIT_NO_EMPTY);$saved=[];foreach($parts as $part){if(preg_match('/^Pasal\s+([\dA-Za-z]+)/iu',trim($part),$m))$saved[]=RegulationContent::updateOrCreate(['regulation_id'=>$v['regulation_id'],'article_number'=>$m[1]],['text_content'=>trim($part)]);}return response()->json(['count'=>count($saved),'contents'=>$saved]);}
}
