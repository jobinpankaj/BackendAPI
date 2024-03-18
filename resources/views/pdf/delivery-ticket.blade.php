<!DOCTYPE html>
<html>
<head>
    <title>Delivery Ticket Document</title>
</head>
<body>
    <?php
            foreach($transportData as $transportData){ ?>
<p><?=$transportData->name ?? null;?></p>
        <table border="1">
            <thead>
                <th>Sr. No.</th>
                <th>Order No.</th>
                <th>Retailer Name</th>
                <th>Product Name</th>
                <th>Batch Number</th>
                <th>Address</th>
                <!-- <th>Shelf Name</th>
                <th>Order Position</th>  -->
                <th>Shipped Quantity</th>

            </thead>
            <tbody>
            <?php 
             $i = 1; 
            foreach($transportData->orderShipments as $orderShipment){
            foreach($orderShipment->shipmentOrderItems as $shipmentOrderItem) { ?>
                <tr>
                    <td><?=$i++;?></td>
                    <td><?=$shipmentOrderItem->orderItems->order->order_reference; ?></td>
                    <td><?=$shipmentOrderItem->orderItems->order->retailerInformation->full_name; ?></td>
                    <td><?=$shipmentOrderItem->orderItems->product->product_name; ?></td>
                    <td><?=$shipmentOrderItem->batch_number; ?></td>
                    <td><?=$shipmentOrderItem->orderItems->order->retailerInformation->userMainAddress->address_1 ;?></td>
                    <td><?=$shipmentOrderItem->shipped_quantity; ?></td>

                </tr>
            <?php } }?>
            </tbody>
        </table>
    <?php } ?>
</body>
</html>