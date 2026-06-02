<?php

namespace App\Enums;

enum FlowType: string
{
    case ScanOcrIdCard = 'scan_ocr_id_card';
    case SelfieCheck = 'selfie_check';
    case PhotoBefore = 'photo_before';
    case PhotoAfter = 'photo_after';
    case VoiceNoteDiagnostic = 'voice_note_diagnostic';
}
