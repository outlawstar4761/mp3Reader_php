<?php

require_once __DIR__ . '/getID3/getid3/getid3.php';
require_once __DIR__ . '/getID3/getid3/write.php';

class Mp3Reader{

    public $getID3;
    public $tagWriter;
    public $pageEncoding;
    public $fileName;
    public $dir;
    public $tagData;
    public $newTagData;
    public $knownTags = array("year","track_number","band","album","genre","title","composer","artist","language","text","date","encoded_by","encoder_settings","publisher","url_user","part_of_a_set","music_cd_identifier","length","comment","unsynchronised_lyric","part_of_a_compilation","copyright_message","content_group_description","release_time","recording_time","conductor","file_type","media_type","url_artist","size","original_artist","bpm","linked_information","commercial_information","original_year","isrc","remixer","initial_key","lyricist","album_sort_order","performer_sort_order","title_sort_order","terms_of_use","url_file","tagging_time");
    public $dbTags = array("title","artist","album","year","track_number","genre","band","length","publisher","bpm");

    public function __construct(){
        $this->getID3 = new getID3;
        $this->tagWriter = new getid3_writetags;
        $this->newTagData = new TagData();
        $this->pageEncoding = "UTF-8";
    }

    public function getTags(){
        $this->getID3->setOption(array('encoding' => $this->pageEncoding));
        $fileInfo = $this->getID3->analyze($this->fileName);
        $this->tagData = $fileInfo["tags_html"]["id3v2"];
        $this->formatTags();
    }

    public function formatTags(){
        $tagData = array();
        foreach($this->tagData as $key=>$value){
            foreach($value as $subKey=>$val){
                if(!in_array($key,$this->knownTags)){
                    die('New Tag Found: ' . $key);
                }
                if(in_array($key,$this->dbTags)){
                    $tagData[$key] = $val;
                }
            }
        }
        $this->tagData = $tagData;
        return $this;
    }

    public function constructTags($title,$artist,$album,$year,$genre,$comment,$track){
        $this->tagData = array(
            "title" => array($title),
            "artist" => array($artist),
            "album" => array($album),
            "year" => array($year),
            "genre" => array($genre),
            "comment" => array($comment),
            "track" => array($track),
        );
    }

    public function writeTags(){
        $this->getID3 = new getID3;
        $this->tagWriter = new getid3_writetags;
        $this->tagWriter->filename = $this->fileName;
        $this->tagWriter->tagformats = array("id3v2.3");
        $this->tagWriter->overwrite_tags = true;
        $this->tagWriter->remove_other_tags = true;
        $this->tagWriter->tag_encoding = $this->pageEncoding;
        $this->tagWriter->tag_data = $this->tagData;
        if($this->tagWriter->WriteTags()){
            $update = true;
            if(!empty($this->tagWriter->warnings)){
                //echo "There are some warnings\n";
                //return 2;
            }
        }
        else{
            $update =  false;
            //echo "Tag update FAILED\n";
        }
        return $update;
    }
}


class TagData{
    public $title;
    public $artist;
    public $album;
    public $year;
    public $genre;
    public $comment;
    public $track;
}

/*Get Tags for an MP3*/
/*$mp3Scanner = new Mp3Scanner();
$mp3Scanner->fileName = "song.mp3";
echo $mp3Scanner->fileName . "\n";
$mp3Scanner->getTags();
print_r($mp3Scanner->tagData);*/

/*WRITE TAGS FOR AN MP3*/
/*$mp3Scanner = new Mp3Scanner();
$mp3Scanner->fileName = "song2.mp3";
$mp3Scanner->constructTags("My new Song","Johns Band","My new Song","1996","balling","My new Song","4");
$mp3Scanner->writeTags();
$mp3Scanner->getTags();
print_r($mp3Scanner->tagData);*/

/*$mp3Scanner->tagData = new TagData;
$mp3Scanner->tagData->title = "My New Song";
$mp3Scanner->tagData->artist = "Johns band";
$mp3Scanner->tagData->album = "My New Song";
$mp3Scanner->tagData->year = "1996";
$mp3Scanner->tagData->genre = "balling";
$mp3Scanner->tagData->comment = "My New Song";
$mp3Scanner->tagData->track = "4";*/