<!doctype html>
<html lang="{{ $profile->cv_language }}" dir="{{ $profile->cv_language === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 46px 48px; }
        body { color: #17191d; font-family: DejaVu Sans, sans-serif; font-size: 10.5px; line-height: 1.55; }
        h1 { font-size: 25px; margin: 0 0 5px; }
        h2 { border-bottom: 1px solid #bcc3cc; font-size: 11px; letter-spacing: .09em; margin: 22px 0 10px; padding-bottom: 5px; text-transform: uppercase; }
        h3 { font-size: 12px; margin: 0 0 4px; }
        p { margin: 4px 0; }
        .role { color: #3f5268; font-size: 13px; }
        .contact { color: #596475; font-size: 9px; margin-top: 7px; }
        .skill-list { color: #253348; }
        .project { margin-bottom: 12px; page-break-inside: avoid; }
        .project-name { font-weight: bold; }
        .project-language { color: #526173; font-weight: normal; }
        .source { color: #4c5f77; font-size: 9px; }
        a { color: #304d6b; text-decoration: none; }
        .fact { margin-bottom: 5px; }
        .cv-template-two-column .cv-paper-section { display: inline-block; vertical-align: top; width: 48%; }
        .cv-template-two-column .cv-paper-section[data-cv-section="summary"] { display: block; width: 100%; }
        .cv-template-ats { font-family: Arial, sans-serif; }
        .cv-template-ats .cv-paper-header { border-bottom: 1px solid #555; }
        .cv-template-ats .cv-paper-section h3 { color: #222; letter-spacing: .04em; }
    </style>
</head>
<body>
    @include('career.cv-content', ['isDocument' => true])
</body>
</html>
