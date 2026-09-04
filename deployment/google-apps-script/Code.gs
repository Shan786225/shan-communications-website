const BUSINESS_HEADERS = [
  'Submission ID', 'Submitted at UTC', 'Status', 'Full name', 'Email', 'Phone',
  'Area of interest', 'Project details', 'Email delivery'
];

const JOB_HEADERS = [
  'Submission ID', 'Submitted at UTC', 'Status', 'Full name', 'Email', 'Phone / WhatsApp',
  'Role of interest', 'Relevant experience', 'Availability', 'CV URL', 'CV file name',
  'Experience summary', 'Email delivery'
];

function doPost(event) {
  try {
    const payload = JSON.parse(event.postData.contents || '{}');
    const properties = PropertiesService.getScriptProperties();
    const expectedSecret = properties.getProperty('SHAN_WEBHOOK_SECRET');
    const spreadsheetId = properties.getProperty('SHAN_SPREADSHEET_ID');
    if (!expectedSecret || !spreadsheetId || payload.secret !== expectedSecret) {
      return jsonResponse({success: false, message: 'Unauthorized'});
    }

    const isJob = payload.formType === 'job';
    const tabName = isJob ? 'Job Applications' : 'Business Inquiries';
    const headers = isJob ? JOB_HEADERS : BUSINESS_HEADERS;
    const spreadsheet = SpreadsheetApp.openById(spreadsheetId);
    const sheet = spreadsheet.getSheetByName(tabName);
    if (!sheet) {
      throw new Error('Required sheet tab is missing: ' + tabName);
    }
    if (sheet.getLastRow() === 0) {
      sheet.appendRow(headers);
    }

    const row = isJob ? [
      payload.publicId, payload.submittedAtUtc, payload.workflowStatus, payload.fullName,
      payload.email, payload.phone, payload.role, payload.experience, payload.availability,
      payload.resumeUrl, payload.resumeFileName, payload.message, payload.emailStatus
    ] : [
      payload.publicId, payload.submittedAtUtc, payload.workflowStatus, payload.fullName,
      payload.email, payload.phone, payload.topic, payload.message, payload.emailStatus
    ];

    const lock = LockService.getScriptLock();
    lock.waitLock(10000);
    try {
      sheet.appendRow(row.map(safeCell));
    } finally {
      lock.releaseLock();
    }
    return jsonResponse({success: true});
  } catch (error) {
    console.error(error);
    return jsonResponse({success: false, message: 'Append failed'});
  }
}

function safeCell(value) {
  const text = value === null || value === undefined ? '' : String(value);
  return /^[=+\-@]/.test(text) ? "'" + text : text;
}

function jsonResponse(payload) {
  return ContentService.createTextOutput(JSON.stringify(payload))
    .setMimeType(ContentService.MimeType.JSON);
}
