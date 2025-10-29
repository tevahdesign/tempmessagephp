<?php

namespace App\Services;

use App\Models\Domain;
use App\Models\Log;
use App\Models\Message;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use App\Models\Stat;
use Carbon\Carbon;
use Ddeboer\Imap\Search\Email\Cc;
use Ddeboer\Imap\Server;
use Ddeboer\Imap\SearchExpression;
use Ddeboer\Imap\Search\Email\To;

class TMail extends Model {
    /**
     * Session key constants
     */
    private const SESSION_EMAIL = 'email';
    private const SESSION_EMAILS = 'emails';

    public static function connectMailBox($imap = null) {
        /**
         * Connect to IMAP mailbox
         * @param array|null $imap
         * @return \Ddeboer\Imap\Connection|null
         */
        $imap = $imap ?? config('app.settings.imap');
        $flags = $imap['protocol'] . '/' . $imap['encryption'];
        $flags .= $imap['validate_cert'] ? '/validate-cert' : '/novalidate-cert';
        $server = new Server($imap['host'], $imap['port'], $flags);
        return $server->authenticate($imap['username'], $imap['password']);
    }

    public static function getMessages($email, $type = 'to', $deleted = []) {
        /**
         * Get messages for an email
         * @param string $email
         * @param string $type
         * @param array $deleted
         * @return array
         */
        if (config('app.settings.engine') === 'delivery') {
            return Message::getMessages($email);
        }
        $connection = self::connectMailBox();
        $mailbox = $connection->getMailbox('INBOX');
        $search = new SearchExpression();
        $search->addCondition($type === 'cc' ? new Cc($email) : new To($email));
        $messages = $mailbox->getMessages($search, \SORTDATE, true);
        $limit = (int) config('app.settings.fetch_messages_limit');
        $response = ['data' => [], 'notifications' => []];
        $count = 0;
        foreach ($messages as $message) {
            if (in_array($message->getNumber(), $deleted, true)) {
                $message->delete();
                continue;
            }
            $data = self::formatMessage($message, $email);
            $response['data'][] = $data['message'];
            if ($data['notification']) {
                $response['notifications'][] = $data['notification'];
            }
            if (++$count >= $limit) break;
        }
        $connection->expunge();
        return $response;
    }

    public static function formatMessage($message, $email = null) {
        /**
         * Format a message object
         * @param object $message
         * @param string|null $email
         * @return array
         */
        $file_types = config('app.settings.allowed_file_types', 'csv,doc,docx,xls,xlsx,ppt,pptx,xps,pdf,dxf,ai,psd,eps,ps,svg,ttf,zip,rar,tar,gzip,mp3,mpeg,wav,ogg,jpeg,jpg,png,gif,bmp,tif,webm,mpeg4,3gpp,mov,avi,mpegs,wmv,flx,txt');
        $allowed = array_map('strtolower', array_map('trim', explode(',', $file_types)));
        $sender = $message->getFrom();
        $date = $message->getDate() ?: (new \DateTime());
        if (!$message->getDate() && $message->getHeaders()->get('udate')) {
            $date->setTimestamp($message->getHeaders()->get('udate'));
        }
        $datediff = new Carbon($date);
        $html = $message->getBodyHtml();
        $text = $message->getBodyText();
        $content = $html ? str_replace('<a', '<a target="blank"', $html)
            : str_replace('<a', '<a target="blank"', str_replace(["\r\n", "\n"], '<br/>', $text));
        $masker = config('app.settings.external_link_masker', '');
        if ($masker) {
            $content = str_replace('href="', 'href="' . $masker . '/?', $content);
        }
        $obj = [
            'subject' => $message->getSubject(),
            'sender_name' => $sender->getName(),
            'sender_email' => $sender->getAddress(),
            'timestamp' => $message->getDate(),
            'date' => $date->format(config('app.settings.date_format', 'd M Y h:i A')),
            'datediff' => $datediff->diffForHumans(),
            'id' => $message->getNumber(),
            'content' => $content,
            'attachments' => []
        ];
        // Blocked sender check
        $domain = explode('@', $obj['sender_email'])[1] ?? '';
        $blocked = in_array($domain, config('app.settings.blocked_domains', []), true);
        if ($blocked) {
            $obj['subject'] = __('Blocked');
            $obj['content'] = __('Emails from') . ' ' . $domain . ' ' . __('are blocked by Admin');
        }
        // Check if in Allowed Domains
        if (config('app.settings.allowed_domains', [])) {
            $allowed = !in_array($domain, config('app.settings.allowed_domains', []), true);
            if ($allowed) {
                $obj['subject'] = __('Blocked');
                $obj['content'] = __('Emails from') . ' ' . $domain . ' ' . __('are blocked by Admin');
            }
        }
        // Attachments
        if ($message->hasAttachments() && !$blocked) {
            $attachments = $message->getAttachments();
            $directory = './tmp/attachments/' . $obj['id'] . '/';
            if (!is_dir($directory)) mkdir($directory, 0777, true);
            foreach ($attachments as $attachment) {
                $filename = $attachment->getFilename();
                $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                if (in_array($extension, $allowed, true)) {
                    $filepath = $directory . $filename;
                    if (!file_exists($filepath)) {
                        file_put_contents($filepath, $attachment->getDecodedContent());
                    }
                    if ($filename !== 'undefined') {
                        $url = env('APP_URL') . str_replace('./', '/', $filepath);
                        $structure = $attachment->getStructure();
                        if (isset($structure->id) && str_contains($obj['content'], trim($structure->id, '<>'))) {
                            $obj['content'] = str_replace('cid:' . trim($structure->id, '<>'), $url, $obj['content']);
                        }
                        $obj['attachments'][] = ['file' => $filename, 'url' => $url];
                    }
                }
            }
        }
        // Notification
        $notification = '';
        if (!$message->isSeen()) {
            $notification = [
                'subject' => $obj['subject'],
                'sender_name' => $obj['sender_name'],
                'sender_email' => $obj['sender_email']
            ];
            if (env('ENABLE_TMAIL_LOGS', true) && $email) {
                file_put_contents(storage_path('logs/tmail.csv'), request()->ip() . "," . date("Y-m-d h:i:s a") . "," . $obj['sender_email'] . "," . $email . PHP_EOL, FILE_APPEND);
            }
        }
        $message->markAsSeen();
        return ['message' => $obj, 'notification' => $notification];
    }

    public static function deleteMessage($id) {
        $connection = TMail::connectMailBox();
        $mailbox = $connection->getMailbox('INBOX');
        $mailbox->getMessage($id)->delete();
        $connection->expunge();
    }

    public static function getEmail($generate = false) {
        /**
         * Get current email from session or generate new
         * @param bool $generate
         * @return string|null
         */
        if (Session::has(self::SESSION_EMAIL)) {
            return Session::get(self::SESSION_EMAIL);
        }
        return $generate ? self::generateRandomEmail() : null;
    }
    public static function getEmails() {
        /**
         * Get all emails from session
         * @return array
         */
        if (Session::has(self::SESSION_EMAILS)) {
            $emails = json_decode(Session::get(self::SESSION_EMAILS), true);
            return is_array($emails) ? $emails : [];
        }
        return [];
    }
    public static function setEmail($email) {
        /**
         * Set current email in session
         * @param string $email
         */
        $emails = self::getEmails();
        if (in_array($email, $emails, true)) {
            Session::put(self::SESSION_EMAIL, $email);
        }
    }
    public static function removeEmail($email) {
        /**
         * Remove email from session
         * @param string $email
         */
        $emails = self::getEmails();
        $key = array_search($email, $emails, true);
        if ($key !== false) {
            array_splice($emails, $key, 1);
        }
        if ($emails) {
            self::setEmail($emails[0]);
            Session::put(self::SESSION_EMAILS, json_encode($emails));
        } else {
            Session::forget(self::SESSION_EMAIL);
            Session::forget(self::SESSION_EMAILS);
        }
    }

    /**
     * this method is used to save emails
     */

    private static function storeEmail($email) {
        /**
         * Store email in session and log
         * @param string $email
         */
        Log::create([
            'ip' => request()->ip(),
            'email' => $email
        ]);
        Session::put(self::SESSION_EMAIL, $email);
        $emails = self::getEmails();
        if (!in_array($email, $emails, true)) {
            self::incrementEmailStats();
            $emails[] = $email;
            Session::put(self::SESSION_EMAILS, json_encode($emails));
        }
    }
    public static function createCustomEmailFull($email) {
        /**
         * Create custom email with full address
         * @param string $email
         * @return string
         */
        [$username, $domain] = explode('@', $email);
        $min = (int) config('app.settings.custom.min');
        $max = (int) config('app.settings.custom.max');
        if (strlen($username) < $min || strlen($username) > $max) {
            $username = (new self)->generateRandomUsername();
        }
        return self::createCustomEmail($username, $domain);
    }

    public static function createCustomEmail($username, $domain) {
        /**
         * Create custom email
         * @param string $username
         * @param string $domain
         * @return string
         */
        $username = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($username));
        $forbidden_ids = config('app.settings.forbidden_ids', []);
        $domains = Domain::getDomainsForCurrentUser();
        if (in_array($username, $forbidden_ids, true)) {
            return self::generateRandomEmail(true);
        }
        $domain = in_array($domain, $domains, true) ? $domain : ($domains[0] ?? '');
        $email = $username . '@' . $domain;
        self::storeEmail($email);
        return $email;
    }

    /**
     * Stats Handling Functions
     */
    public static function incrementEmailStats($count = 1) {
        Stat::storeEmailsCreated($count);
    }

    public static function incrementMessagesStats($count = 1) {
        Stat::storeMessagesReceived($count);
    }

    public static function generateRandomEmail($store = true) {
        /**
         * Generate random email
         * @param bool $store
         * @return string
         */
        $tmail = new self;
        $email = $tmail->generateRandomUsername() . '@' . $tmail->getRandomDomain();
        if ($store) {
            self::storeEmail($email);
        }
        return $email;
    }

    private function generateRandomUsername() {
        $start = config('app.settings.random.start', 0);
        $end = config('app.settings.random.end', 0);
        if ($start == 0 && $end == 0) {
            return $this->generatePronounceableWord();
        }
        return $this->generatedRandomBetweenLength($start, $end);
    }

    protected function generatedRandomBetweenLength($start, $end) {
        $length = rand($start, $end);
        return $this->generateRandomString($length);
    }

    private function getRandomDomain() {
        $domains = Domain::getDomainsForCurrentUser();
        $count = count($domains);
        return $count > 0 ? $domains[rand(0, $count - 1)] : '';
    }

    private function generatePronounceableWord() {
        $c  = 'bcdfghjklmnprstvwz'; // consonants
        $v  = 'aeiou';              // vowels
        $a  = $c . $v;              // both
        $random = '';
        for ($j = 0; $j < 2; $j++) {
            $random .= $c[rand(0, strlen($c) - 1)];
            $random .= $v[rand(0, strlen($v) - 1)];
            $random .= $a[rand(0, strlen($a) - 1)];
        }
        return $random;
    }

    private function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
