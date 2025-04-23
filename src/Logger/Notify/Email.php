<?php

namespace Sparky7\Logger\Notify;

use DateTime;
use DateTimeZone;
use PHPMailer\PHPMailer\PHPMailer;
use Sparky7\Logger\LoggerNotify;

/**
 * Email handler.
 */
class Email extends LoggerNotify
{
    private $PHPMailer;

    /**
     * Constructor.
     *
     * @param string    $title     Title
     * @param PHPMailer $PHPMailer PHPMailer instance
     */
    public function __construct($title, PHPMailer $PHPMailer)
    {
        $this->PHPMailer = $PHPMailer;
        $this->title = $title;
    }

    /**
     * Sends log notification.
     */
    public function send()
    {
        $this->PHPMailer->Subject = $this->message;
        $this->PHPMailer->Body = $this->format();

        $this->PHPMailer->send();
    }

    /**
     * Format log data.
     */
    public function format()
    {
        switch ($this->level) {
            case 'notice':
                $style1 = 'color: #3c763d; background-color: #dff0d8; border-color: #d6e9c6;';
                $style2 = 'background-color: #5cb85c;';
                break;
            case 'info':
            case 'debug':
                $style1 = 'color: #31708f; background-color: #d9edf7; border-color: #bce8f1;';
                $style2 = 'background-color: #5bc0de;';
                break;
            case 'alert':
            case 'warning':
                $style1 = 'color: #8a6d3b; background-color: #fcf8e3; border-color: #faebcc;';
                $style2 = 'background-color: #f0ad4e;';
                break;
            default:
                $style1 = 'color: #a94442; background-color: #f2dede; border-color: #ebccd1;';
                $style2 = 'background-color: #d9534f;';
                break;
        }

        $format = 'F j, Y @ g:i:s a T';
        $dateUTC = new DateTime('now', new DateTimeZone('UTC'));
        $dateCST = new DateTime('now', new DateTimeZone('America/Mexico_City'));
        $datePST = new DateTime('now', new DateTimeZone('America/Tijuana'));

        return '
            <!DOCTYPE html>
            <html>
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
            </head>
            <body style="font-family: Arial,sans-serif; font-size: 14px; color: #333; background-color: #fff;">
                <h1 style="margin: .67em 0; font-size: 36px; font-weight: 500; line-height: 1.1; color: inherit;">
                    ' . $this->title .
                    ' <span style="' . $style2 . '; color: #fff;  border-radius: 8px; padding: 7px;">' .
                    ucfirst(strtolower($this->level)) . '</span>
                </h1>
                <small>Date: <b>' . $dateUTC->format($format) . '</b></small><br>
                <small>Date: <b>' . $dateCST->format($format) . '</b></small><br>
                <small>Date: <b>' . $datePST->format($format) . '</b></small><br>
                <hr style="border: 0; height: 0;
                    border-top: 1px solid rgba(0, 0, 0, 0.1);
                    border-bottom: 1px solid rgba(255, 255, 255, 0.3);">
                <div style="' . $style1 . '; font-size: 1.2em; font-weight: 200; border-radius: 4px; padding: 18px;">
                    <i>' . $this->message . '</i>
                </div>
                <hr style="border: 0; height: 0;
                    border-top: 1px solid rgba(0, 0, 0, 0.1);
                    border-bottom: 1px solid rgba(255, 255, 255, 0.3);">
                <p>
                    ' . $this->formatContext(['tags' => $this->tags], 'panel-info') . '
                    ' . $this->formatContext($this->context) . '
                </p>
            </body>
            </html>
        ';
    }

    /**
     * Format context.
     *
     * @param array $context Context array
     *
     * @return string Formatted context
     */
    private function formatContext(array $context)
    {
        $panel = '';
        foreach ($context as $key => $value) {
            if ((is_array($value) && count($value) > 0) || (!is_array($value))) {
                switch (strtolower($key)) {
                    case 'tags':
                        $style = 'color: #fff; background-color: #337ab7; border-color: #337ab7 #337ab7;';
                        break;
                    default:
                        $style = 'color: #333; background-color: #f5f5f5; border-color: #ddd #ddd;';
                        break;
                }

                $panel .= '<table width="100%" cellpadding="10" cellspacing="0">';
                $panel .= '<thead>';
                $panel .= '<th style="' . $style . '; font-size: 18px; font-weight: 500; padding: 8px;" colspan="2">';
                $panel .= strtoupper($key) . '</th>';
                $panel .= '</thead>';
                $panel .= '<tbody>' . self::formatValues($value) . '</tbody>';
                $panel .= '</table>';
                $panel .= '<hr style="border: 0; height: 0; border-top: 1px solid rgba(0, 0, 0, 0.1);';
                $panel .= ' border-bottom: 1px solid rgba(255, 255, 255, 0.3);">';
            }
        }

        return $panel;
    }

    /**
     * Format values.
     *
     * @param any $value Value
     *
     * @return string Formatted values
     */
    private function formatValues($value)
    {
        if (!is_array($value)) {
            return $value;
        }

        $tbody = '';
        foreach ($value as $key => $value2) {
            $style = 'border-top: 1px solid #ddd;';
            if (is_array($value2) || is_object($value2)) {
                $tbody .= '<tr><td width="180" style="' . $style . '">';
                $tbody .= ucfirst(strtolower($key)) . '</td><td style="' . $style . '"><pre>';
                $tbody .= json_encode($value2, JSON_PRETTY_PRINT) . '</pre></td></tr>';
            } else {
                $tbody .= '<tr><td width="180" style="' . $style . '">';
                $tbody .= ucfirst(strtolower($key)) . '</td><td style="' . $style . '">';
                $tbody .= '<samp>' . $value2 . '</samp></td></tr>';
            }
        }

        return $tbody;
    }
}
