<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expired Employee Contracts Notification</title>
    <style>
        /* Reset styles */
        body, body * {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;            
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            font-size: 10px; /* Set a base font size for the entire email */
        }
        th {
            background-color: #f2f2f2;            
        }
        /* Body styles */
        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo img {
            max-width: 150px;
        }
        .content {
            margin-bottom: 20px;
        }
        .table-wrapper {
            margin-top: 20px;
        }
        /* Button styles */
        .btn {
            display: inline-block;
            padding: 10px 20px;
            text-decoration: none;
            background-color: #007bff;
            color: #fff;
            border-radius: 3px;
            text-align: center;
        }
        .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="https://i.imgur.com/Qkg30CL.png" alt="Company Logo">
        </div>
        <div class="content">
            <h2>Expired Employees Contract - BTJ</h2>
            <p>Dear All,</p>
            <p>The following is a list of employees whose contract will expire in less than 30 days or have already expired:</p>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Employee Number</th>
                        <th>Site</th>
                        <th>Department</th>
                        <th>Employee Name</th>
                        <th>End Contract</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($expiredContracts as $index => $contract)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $contract->employee_number }}</td>
                            <td>{{ $contract->siteName }}</td>
                            <td>{{ $contract->deptName }}</td>
                            <td>{{ $contract->employee_name }}</td>
                            <td>{{ \Carbon\Carbon::parse($contract->end_contract)->format('d-m-Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p style="margin-top: 20px;">Thank you.</p>
        <p style="margin-top: 20px;">Warm Regards,</p>
        <p style="margin-top: 5px;">Admin IT-HRIS-BTJ</p>
    </div>
</body>
</html>
