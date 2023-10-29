<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Process Setup</title>
</head>
<body>
    <h2>Process Setup </h2><hr/>

    <form action="" method="POST">
        @csrf
        <div>
            <label name="ID">Unique ID(MET)</label>
            <input type="text" name="meterno" placeholder="Enter UniqueID" />

        </div>

        <div>
            <label name="ID">Type</label>
            <input type="text" name="account_type" placeholder="Enter Type" />


        </div>


        <div>
            <label name="ID">Funding</label>
            <input type="number" name="amount" placeholder="Enter Funding" />

        </div>

        <div>
           <button class="btn btn-sm">Submit</button>
        </div>


    </form>
</body>
</html>