<?php
/**
 * Created by PhpStorm.
 * User: Pavithra
 * Date: 1/21/15
 * Time: 10:39 PM
 */
?>
<div class="col-md-12 col-sm-12 col-xs-12" xmlns="http://www.w3.org/1999/html" xmlns="http://www.w3.org/1999/html">
    <h3 class="text-theme">Transaction History</h3>
    <hr>
    <table class="table table-responsive table-striped table-hover">
    <thead>
    <tr>
        <th>Transaction ID</th>
        <th>Transaction Member Id</th>
        <th>Mode</th>
        <th>Amount</th>
        <th>Currency</th>
        <th>Bank</th>
        <th>Date (dd-mm-yy)</th>
        <th>Verified</th>
        <th>Remarks</th>
    </tr>
    </thead>
    <tbody>
<?php
foreach($transactions as $transaction)
{

    ?>
    <tr>
        <td>
        <?php echo $transaction->transaction_id;?>
        </td>
    <td>
        <?php echo $transaction->transaction_member_id;?>
    </td>
    <td>
        <?php
        if($transaction->transaction_mode != null)
            echo $transactionModes[$transaction->transaction_mode]->transaction_mode_name;
        ?>
    </td>
    <td>
        <?php echo $transaction->transaction_amount;?>
    </td>
    <td>
        <?php echo $transaction->transaction_currency;?>
    </td>
    <td>
        <?php echo $transaction->transaction_bank;?>
    </td>
    <td>
        <?php
        echo date_format(date_create($transaction->transaction_date), 'd-m-Y');
        ?>
    </td>
    <td>
        <?php if($transaction->is_verified==0)
            echo "Verification Pending";
        else if($transaction->is_verified==1)
            echo "Accepted";
        else if($transaction->is_verified==2)
            echo "Rejected";
        ?>
    </td>
    <td>
    <?php echo $transaction->transaction_remarks;?>
    </td>
    </tr>
<?php
}
?>
    </tbody>
    </table>
