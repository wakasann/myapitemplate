<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * 連接的XMPP服務器是openfire,只是一個發送消息的demo job
 * Class XMPPDemoMessage
 * @package App\Jobs
 */
class XMPPDemoMessage  extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $room_full_jid;
    protected $room_name = 'demo@conference.ubuntu';
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }


    function on_auth_success_callback(){
        $this->client->set_status("available!", "dnd", 10);

        $nickname = $this->client->full_jid->to_string();
        if(strpos($nickname,"/") !== false){
            $jids = explode("/", $nickname);
            $nickname = $jids[1];
        }else{
            $nickname = 'demo'.time();
        }
        $_room_full_jid = $this->room_name."/".$nickname;
        $room_full_jid = new \XMPPJid($_room_full_jid);
        $this->client->xeps['0045']->join_room($room_full_jid);


        $xml = new \JAXLXml('message');
        $_room_full_jid2 = "demo@conference.ubuntu";
        $to2 = new \XMPPJid($_room_full_jid2);
        $xml->attrs(array(
            'from'=>$this->client->full_jid->to_string(),
            'to'=>$to2->to_string(),
            'type'=>'groupchat'
        ));
        $xml->c('body')->t('test message');
        $this->client->send($xml);
        $this->client->send_end_stream();
    }

    function on_groupchat_message_callback($stanza){

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // if ($this->attempts() > 3) {
        //     $this->release(10);
        // }
        //
        $this->client = new \JAXL(array(
            'jid' => '',
            'pass' => '',
            'host'=>'localhost',
            'auth_type'=>'ANONYMOUS'
        ));
        $_room_full_jid = $this->room_name;
        $this->room_full_jid = new \XMPPJid($_room_full_jid);

        $this->client->require_xep(array(
            '0045',     // MUC
            '0203',     // Delayed Delivery
            '0199',  // XMPP Ping
        ));

        $this->client->add_cb('on_auth_success', array($this,'on_auth_success_callback'));

        $this->client->add_cb('on_groupchat_message', array($this,'on_groupchat_message_callback'));

        $this->client->start();

    }
}