<?php

namespace TeaBot;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package \TeaBot
 * @version 6.2.0
 */
trait ResponseRoutes
{
    /**
     * @return bool
     */
    private function execRoutes(): bool
    {
        /**
         * Start command.
         */
        if (preg_match("/^(\/|\!|\~|\.)start$/Usi", $this->data["text"])) {
            if ($this->stExec(Responses\Start::class, "start")) {
                return true;
            }
        }

        /**
         * Help command.
         */
        if (preg_match("/^(\/|\!|\~|\.)help$/Usi", $this->data["text"])) {
            if ($this->stExec(Responses\Help::class, "help")) {
                return true;
            }
        }

        /**
         * Qur'an command.
         */
        if (preg_match("/^(?:\/|\!|\~|\.)(?:quran )(\d{1,3}):(\d{1,3})$/Usi", $this->data["text"], $m)) {
            if ($this->stExec(Responses\Quran::class, "quran", [(int)$m[1], (int)$m[2]])) {
                return true;
            }
        }

        /**
         * Debug command.
         */
        if (preg_match("/^(?:\/|\!|\~|\.)(?:debug)$/Usi", $this->data["text"])) {
            if ($this->stExec(Responses\Debug::class, "debug")) {
                return true;
            }
        }

        /**
         * Login AMIKOM.
         */
        if (preg_match("/^(?:\/|\!|\~|\.)?(?:amikom\s+login\s+)(\S+)(?:\s+)(\S+)$/i", $this->data["text"], $m)) {
            if ($this->stExec(Responses\Amikom\Mahasiswa::class, "login", [$m[1], $m[2]])) {
                return true;
            }
        }

        /**
         * Jadwal Kuliah.
         */
        if (preg_match("/^(?:\/|\!|\~|\.)?(?:jadwal)$/i", $this->data["text"], $m)) {
            if ($this->stExec(Responses\Amikom\Mahasiswa::class, "jadwal")) {
                return true;
            }
        }

        /**
         * Jadwal Kuliah.
         */
        if (preg_match("/^(?:\/|\!|\~|\.)?(?:jadwal\s+)(senin|selasa|rabu|kamis|jum'?at|sabtu)$/i", $this->data["text"], $m)) {
            if ($this->stExec(Responses\Amikom\Mahasiswa::class, "jadwal", [$m[1]])) {
                return true;
            }
        }

        /**
         * Absen/Presensi
         */
        if (preg_match("/^(?:\/|\!|\~|\.)?(?:absen|presensi)(?:\s+)(.+?)$/i", $this->data["text"], $m)) {
            if ($this->stExec(Responses\Amikom\Mahasiswa::class, "presensi", [$m[1]])) {
                return true;
            }
        }

        /**
         * Titip Absen.
         */
        if (preg_match("/^(?:\/|\!|\~|\.)?(?:tipsen)(?:\s+)(\S+?)(?:\s+)(.+?)$/is", $this->data["text"], $m)) {
            $m[2] = str_replace("\n", " ", $m[2]);
            if ($this->stExec(Responses\Amikom\Mahasiswa::class, "tipsen", [$m[1], $m[2]])) {
                return true;
            }
        }

        /**
         * Google translate.
         */
        if (preg_match("/^(?:\/|\!|\~|\.)?(?:tr)\s(\S+)\s(\S+)\s(.+)$/Usi", $this->data["text"], $m)) {
            if ($this->stExec(Responses\GoogleTranslate::class, "translate", [$m[1], $m[2], $m[3]])) {
                return true;
            }
        }

        /**
         * Google translate reply.
         */
        if (preg_match("/^(?:\/|\!|\~|\.)?(?:tl?r)\s(\S+)\s(\S+)$/Usi", $this->data["text"], $m) &&
            (
                (
                    isset($this->data["reply"]["text"]) &&
                    ($m[3] = trim($this->data["reply"]["text"]))
                ) ||
                (
                    isset($this->data["reply"]["caption"]) &&
                    ($m[3] = trim($this->data["reply"]["caption"]))
                )
            )
        ) {
            if ($this->stExec(Responses\GoogleTranslate::class, "translate", [$m[1], $m[2], $m[3]])) {
                return true;
            }
        }

        /**
         * Calculus.
         */
        if (preg_match("/^(?:\/|\!|\~|\.)?([a-z\d]{4})(?:(?:[\\s\\n])+)(.+?)$/si", $this->data["text"], $m)) {
            $m[2] = str_replace("\n", " ", $m[2]);
            switch ($m[1]) {
                case "c001":
                    if ($this->stExec(Responses\Calculus::class, "c001", [$m[2]])) {
                        return true;
                    }
                break;

                case "c002":
                    if ($this->stExec(Responses\Calculus::class, "c002", [$m[2]])) {
                        return true;
                    }
                break;

                case "cr02":
                    if ($this->stExec(Responses\Calculus::class, "cr02", [$m[2]])) {
                        return true;  
                    }
                break;

                case "cyf4":
                    if ($this->stExec(Responses\Calculus::class, "cyf4", [$m[2]])) {
                        return true;  
                    }
                break;

                case "lxt0":
                    if ($this->stExec(Responses\Calculus::class, "lxt0", [$m[2]])) {
                        return true;  
                    }
                break;

                case "lxt1":
                    if ($this->stExec(Responses\Calculus::class, "lxt1", [$m[2]])) {
                        return true;  
                    }
                break;

                default:
                break;
            }
        }

        return false;
    }
}
