# 422 Error Fix - Contract Creation

## Problem
The frontend was sending a 422 Unprocessable Content error when creating contracts because:

1. **Field Mismatch**: Frontend used custom tender fields (`tender_number`, `project_name`, `supplier_contractor`) but backend expected legal contract fields (`contract_title`, `contract_type`, `party_a`, `party_b`)

2. **Date Validation Issue**: Empty date strings `""` were being sent instead of `null`, which failed Laravel's date casting validation

## Request That Failed
```json
{
  "tender_number": "FA/2024/2025/ 155/TR118/G/27/ LOT 3",
  "project_name": "SUPPLY OF ANGIOSUITE CONSUMABLES",
  "supplier_contractor": "PRADLE MEDICS (T) LIMITED",
  "contract_amount": 4999.99,
  "contract_year": 2025,
  "date_received": "",
  "date_vetted": "",
  "date_signed": "",
  "commencement_date": "",
  "completion_date": "",
  "status": "Draft",
  "notes": "",
  "created_by": "System"
}
```

## Solution Applied

### Files Modified
1. **CreateContract.js** - Fixed field mapping for new contracts
2. **EditContract.js** - Fixed field mapping for editing contracts

### Changes Made

#### 1. Field Mapping (CreateContract.js)
```javascript
// Map frontend fields to backend schema
const contractData = {
  contract_title: formData.project_name,
  contract_type: 'Tender',
  party_a: 'Government/Organization',
  party_b: formData.supplier_contractor,
  contract_value: formData.contract_amount ? parseFloat(formData.contract_amount) : null,
  currency: 'TZS',
  signing_date: formData.date_signed || null,  // Empty string → null
  effective_date: formData.commencement_date || null,
  expiry_date: formData.completion_date || null,
  status: formData.status,
  notes: formData.notes,
  description: `Tender Number: ${formData.tender_number}\nYear: ${formData.contract_year}\nDate Received: ${formData.date_received || 'N/A'}\nDate Vetted: ${formData.date_vetted || 'N/A'}`,
  created_by: currentUser?.id || null
};
```

#### 2. Reverse Mapping (EditContract.js)
When loading existing contracts, the backend fields are mapped back to frontend fields:
```javascript
setFormData({
  tender_number: extractTenderNumber(contract.description) || contract.contract_number,
  project_name: contract.contract_title,
  supplier_contractor: contract.party_b,
  contract_amount: contract.contract_value,
  date_signed: formatDate(contract.signing_date),
  commencement_date: formatDate(contract.effective_date),
  completion_date: formatDate(contract.expiry_date),
  // ... other fields
});
```

## Backend Schema Reference

The `legal_contracts` table expects:
- **contract_title** (required) - Project name
- **contract_type** (required) - Type: Tender, Service Agreement, etc.
- **party_a** (required) - First party (e.g., Government)
- **party_b** (required) - Second party (e.g., Contractor)
- **contract_value** (nullable decimal) - Contract amount
- **signing_date** (nullable date) - Date signed
- **effective_date** (nullable date) - Commencement date
- **expiry_date** (nullable date) - Completion date
- **status** (nullable) - Draft, Active, etc.

## Testing
After this fix, contract creation should work properly. The tender-specific fields are preserved in the `description` field for reference.

## Date Handling
✅ Empty date inputs now send `null` instead of `""`
✅ Laravel's date casting accepts `null` values
✅ Date fields are optional and won't cause validation errors
