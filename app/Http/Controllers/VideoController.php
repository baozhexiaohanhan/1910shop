<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Video;

class VideoController extends Controller
{
    public function crontab()
    {
        $data = Video::where('status',0)->orderBy("id","desc")->get();
        echo " 开始转码 ： ". date("Y-m-d H:i:s");echo '</br>';
        if($data)
        {
            foreach($data as $k=>$v)
            {
                $goods_id = $v->goods_id;
                Video::where('goods_id',$goods_id)->update(['status'=>1]);
                fastcgi_finish_request();//冲刷所有响应的数据给客户端
                $video_file = $v->goods_video;
                $m3u8_file = $goods_id.'.m3u8';
                $ts_file = $goods_id.'_%03d.ts';
                $ts_second = 20;

                $cmd = "cd storage && ffmpeg -i {$video_file} -codec:v libx264 -codec:a mp3 -map 0 -f ssegment -segment_format mpegts -segment_list $m3u8_file -segment_time $ts_second $ts_file";
                shell_exec($cmd);

                $m3u8_file_path = $m3u8_file;
                Video::where('goods_id',$goods_id)->update(['status'=>2,'goods_m3u8'=>$m3u8_file_path]);
            }
        }
    }
}
