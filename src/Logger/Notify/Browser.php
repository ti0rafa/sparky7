<?php

namespace Sparky7\Logger\Notify;

use Sparky7\Helper\Buffer;
use Sparky7\Logger\LoggerNotify;

/**
 * Browser handler.
 */
class Browser extends LoggerNotify
{
    /**
     * Sends log notification.
     */
    public function send()
    {
        switch ($this->level) {
            case 'notice':
                $class = 'success';
                break;
            case 'info':
            case 'debug':
                $class = 'info';
                break;
            case 'alert':
            case 'warning':
                $class = 'warning';
                break;
            default:
                $class = 'danger';
                break;
        }

        Buffer::clear();
        Buffer::gzip();

        echo '
            <!DOCTYPE html>
            <html>
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
                <title>' . $this->title . '</title>
                <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css" rel="stylesheet">
            </head>
            <body>
                <div class="container">
                    <h1>' . $this->title . ' <span class="label label-' . $class . '">' .
                        ucfirst(strtolower($this->level)) .
                    '</span></h1>
                    <div class="row">
                        <div class="col-md-6">
                            <small>Date: <b>' . date('l F j, Y @ g:i:s a') . '</b></small>
                        </div>
                    </div>
                    <hr>
                    <div class="alert alert-' . $class . ' text-justify" style="font-size: 1.5em; font-weight: 200;">
                        ' . $this->message . '
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            ' . $this->formatContext(['tags' => $this->tags], 'panel-info') . '
                            ' . $this->formatContext($this->context) . '
                        </div>
                    </div>
                </div>
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
                        $panel_class = 'primary';
                        break;
                    default:
                        $panel_class = 'default';
                        break;
                }

                $panel .= '
                    <div class="panel panel-' . $panel_class . '">
                        <div class="panel-heading"><b>' . strtoupper($key) . '</b></div>
                        <div class="panel-body">' . self::formatValues($value) . '</div>
                    </div>
                    <hr>
                ';
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

        $table = '<table class="table table-condensed table-hover">';
        foreach ($value as $key => $value2) {
            if (is_array($value2) || is_object($value2)) {
                $table .= '<tr><td width="180"><small>' .
                    ucfirst(strtolower($key)) .
                    '</small></td><td><pre>' .
                    json_encode($value2, JSON_PRETTY_PRINT) .
                    '</pre></td></tr>';
            } else {
                $table .= '<tr><td width="180"><small>' .
                    ucfirst(strtolower($key)) .
                    '</small></td><td><samp>' .
                    $value2 .
                    '</samp></td></tr>';
            }
        }
        $table .= '</table>';

        return $table;
    }
}
