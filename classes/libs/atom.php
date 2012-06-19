<?php

class Feedwriter_Atom
{
    private $meta = array();
    private $author = array();
    private $items = array();

    public function __construct($title = NULL, $link = NULL)
    {
        $this->meta = array(
            'link'      => ($link) ? $link :
                URL::site(Kohana_Request::detect_uri(), true),
            'title'     => ($title) ? $title : "Atom Feed",
            'subtitle'  => "A generic Atom feed.",
            'id'        => ($link) ? $link :
                URL::site(Kohana_Request::detect_uri(), true),
            'rights'    => "Copyright (c) 2008-2012 Ushahidi Inc ".
                "<http://ushahidi.com>",
            'updated'   => "Thu, 01 Jan 1970 00:00:00 +0000",
            'generator' => "SwiftRiver FeedWriter",
            'logo'      => URL::site('media/img/logo-swiftriver.png', true)
        );

        $this->author = array(
            'name' => 'SwiftRiver',
            'uri'  => URL::site('', true)
        );
    }

    public function setTitle($title)
    {
        $this->meta['title'] = $title;
    }

    public function setLink($link)
    {
        $this->meta['link'] = $link;
        $this->meta['id'] = $link;
    }

    public function setDescription($description)
    {
        $this->meta['subtitle'] = $description;
    }

    public function setCopyright($copyright)
    {
        $this->meta['rights'] = $copyright;
    }

    public function setUpdated($updated)
    {
        $this->meta['updated'] = date(DATE_ATOM, strtotime($updated));
    }
    
    public function setAuthor($author, $uri)
    {
        $this->author['name'] = $author;
        $this->author['uri'] = $uri;
    }

    public function addItem($params)
    {
        $this->items[] = array(
            'title'   => $params['title'],
            'id'      => $params['id'],
            'link'    => $params['link'],
            'content' => $params['content'],
            'updated' => date(DATE_ATOM, strtotime($params['time'])),
            'author'  => $params['author']
        );
    }

    public function generate()
    {
        $atom  = '<?xml version="1.0" encoding="utf-8"?>'."\n";
        $atom .= '<feed xmlns="http://www.w3.org/2005/Atom"><link href="'.
            URL::site(Kohana_Request::detect_uri(), true).'" rel="self" />';

        foreach ($this->meta as $key => $value)
        {
            if ($key != 'link')
                $atom .= '<'.$key.'>'.htmlentities($value).'</'.$key.'>';
            else
                $atom .= '<'.$key.' href="'.$value.'" />';
        }

        $atom .= '<author>';
        foreach ($this->author as $key => $value)
            $atom .= '<'.$key.'>'.htmlentities($value).'</'.$key.'>';
        $atom .= '</author>';

        foreach ($this->items as $key => $value)
        {
            $atom .= '<entry>';
            foreach ($value as $k => $v)
            {
                switch ($k)
                {
                    case 'author':
                        $atom .= '<'.$k.'><name>'.htmlentities($v).'</name></'.$k.'>';
                        break;
                    case 'link':
                        $atom .= '<'.$k.' href="'.$v.'" />';
                        break;
                    case 'content':
                        $atom .= '<'.$k.' type="html">'.htmlentities($v).'</'.$k.'>';
                        break;
                    default:
                        $atom .= '<'.$k.'>'.htmlentities($v).'</'.$k.'>';
                }
            }
            $atom .= '</entry>';
        }

        $atom .= '</feed>';
        return $atom;
    }
}

?>