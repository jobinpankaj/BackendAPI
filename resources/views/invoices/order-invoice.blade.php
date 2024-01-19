<!-- <!DOCTYPE html>
<html>
<head>
    <title>Delivery Ticket Document</title>
</head>
<body>
   
      {{$orderData->supplierInformation->first_name}}
      {{$totalOrderTax}}
   
</body>
</html> -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order invoice</title>
</head>

<body>
    <div style="width:96%; margin: 0 auto; padding:20px 10px; max-width:960px">
        <table colspan="0" rowspan="0" style="width: 100%; font-family:'Ubuntu', sans-serif; font-weight: 400;">
            <thead>
                <tr>
                    <th style="font-size: 16px; color: #414141; text-align: start; padding-bottom: 15px; width: 350px;">
                        <div style="display: table;">
                            <div style="width: 15%; display: table-cell;">
                                <img src="{{ asset('images/barcode.png') }}" alt="barcode"
                                    style=" padding-bottom: 10px;max-width:130px;">
                                Scan QR code to review Invoice
                            </div>
                            <div style="width:15%; display: table-cell;">
                                <img src="{{asset('images/buvons-local-pro.png')}}" alt="logo"
                                    style=" padding-bottom: 10px; max-width: 130px;">
                            </div>
                        </div>
                    </th>
                    <th style=" text-align: center;">
                        <img src="{{asset('/images/pit-caribou.jpg')}}" alt="pit-caribou"
                            style="vertical-align: bottom; width: 15%;">

                    </th>
                    <th
                        style=" text-align: start; font-size: 20px; font-weight: 400; padding-bottom: 15px; width:250px;">
                        <h4 style="margin: 0 0 5px; font-size:24pxpx; line-height: 1;">Purchase Order</h4>
                        <h3 style="margin: 0 0 5px; font-size:40px; line-height: 1;"><strong>#{{$invoiceNumber}}</strong></h3>
                        <p style="margin: 0 0 5px; font-size:22pxpx; line-height: 1;">{{{$orderData->created_at}}}</p>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="padding-top: 20px; padding-right: 20px;">
                        <h5 style="color: #000; margin: 0 0 10px; font-size: 22px;">Supplier</h5>
                        <h5 style="color: #000; margin: 0 0 10px; font-size: 24px;"><b>{{$orderData->supplierInformation->full_name}}</b>
                        </h5>
                        <p style="font-size: 18px; margin: 0 0 30px;  color: #000; line-height: 1.5;">27 Rue de l'Anse
                            <br /> Cap-d'Espoir
                            {QC) G0C 1 GO</p>

                        <p style="font-size: 18px; margin: 0;  color: #000; line-height: 1.5;">c/o #id: 1082381
                        </p>
                        <p style="font-size: 18px; margin: 0;  color: #000; line-height: 1.5;">41 8-782-1444 </p>
                        <p style="font-size: 18px; margin: 0 0 20px;  color: #000; line-height: 1.5;">
                            ventes<i>@</i>pitca ribou.com
                        </p>

                        <hr style="margin-bottom: 30px;">

                        <h5 style="color: #000; margin: 0 0 10px; font-size: 22px;">Distributed by </h5>
                        <p style="font-size: 18px; margin: 0;  color: #000; line-height: 1.5;">Bucke Distribution
                        </p>
                        <p style="font-size: 18px; margin: 0px;  color: #000; line-height: 1.5;">(450) 641-4848 </p>
                        <p style="font-size: 18px; margin: 0 0 10px;  color: #000; line-height: 1.5;">
                            info<i>@</i>bucke.ca
                        </p>
                    </td>
                    <td colspan="2" style="padding-top: 35px; padding-left: 50px; vertical-align: baseline;">
                        <table style="border-spacing: 0; width: 100%;" cellspacing="0" cellpadding="0">
                            <tbody>
                                <tr>
                                    <td style="padding:20px 20px 10px; width: 50%;">
                                        <h5 style="color: #000; margin: 0 0 10px; font-size: 22px;">Supplier</h5>
                                        <p style="font-size: 18px; margin: 0;  color: #000; line-height: 1.5;">IGA
                                            Marchedu Faubourg St-Amable </p>
                                    </td>
                                    <td style="padding:20px  20px 10px;">
                                        <h5 style="color: #000; margin: 0 0 10px; font-size: 22px;">Type</h5>
                                        <h6 style="font-size:20px; margin: 0;  color: #8154dd; line-height: 1.5;">CAD
                                        </h6>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <hr>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:20px 20px 10px;">
                                        <h5 style="color: #000; margin: 0 0 10px; font-size: 22px;">Bill to </h5>
                                        <p style="font-size: 18px; margin: 0 0 20px;  color: #000; line-height: 1.5;">
                                            525 rue
                                            Principale <br> Saint-Amable (QC) J0L 1 NO
                                        </p>
                                        <p style="font-size: 18px; margin: 0px 0 2px;  color: #000; line-height: 1.5;">
                                            (450)
                                            641-4848 </p>
                                        <p style="font-size: 18px; margin: 0 0 10px;  color: #000; line-height: 1.5;">
                                            info<i>@</i>bucke.ca
                                        </p>
                                    </td>
                                    <td style="padding:20px  20px 10px; vertical-align: baseline;">
                                        <h5 style="color: #000; margin: 0 0 10px; font-size: 22px;">Deliver to</h5>
                                        <p style="font-size: 18px; margin: 0 0 20px;  color: #000; line-height: 1.5;">
                                            525 rue
                                            Principale <br> Saint-Amable (QC) J0L 1 NO
                                        </p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td colspan="3" style="padding-top: 30px; text-align: center;">
                        <table cellpadding="0" cellspacing="0" style="border-spacing: 0; width: 100%;">
                            <thead>
                                <tr>
                                    <th
                                        style=" background: #8154dd; color: #fff; font-size: 22px; text-transform: capitalize; border-top-left-radius: 10px; border-bottom-left-radius: 10px; padding: 20px; width: 50%; text-align: start;">
                                        Item</th>
                                    <th
                                        style=" background: #8154dd; color: #fff; font-size: 22px; text-transform: capitalize;  padding: 20px 15px;">
                                        Quantity(8)</th>
                                    <th
                                        style=" background: #8154dd; color: #fff; font-size: 22px; text-transform: capitalize;  padding: 20px 15px;">
                                        Price</th>
                                    <th
                                        style=" background: #8154dd; color: #fff; font-size: 22px; text-transform: capitalize;  padding: 20px 15px;border-top-right-radius: 10px; border-bottom-right-radius: 10px;">
                                        Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td
                                        style="padding: 15px 20px; text-align: start; border-bottom: 1px solid #e7e7e7;">
                                        <h5 style="font-size: 15px; color:#000; margin: 0 0 10px;">BONNE AVENTURE BOTTLE
                                            REAL ALE 16.90702 X 12</h5>
                                        <p style="color: #9370db; font-size: 14px; margin: 0; line-height: 1.2;">BATCH
                                            # 12345678910 SAP# Metro 123456 Lowbla: 123456 Sobeys: 12345</p>
                                    </td>
                                    <td style=" border-bottom: 1px solid #e7e7e7;">
                                        3
                                    </td>
                                    <td style=" border-bottom: 1px solid #e7e7e7;">
                                        CA$90.00
                                    </td>
                                    <td style=" border-bottom: 1px solid #e7e7e7;">
                                        CA$270.00
                                    </td>
                                </tr>
                                <tr>
                                    <td
                                        style="padding: 15px 20px; text-align: start; border-bottom: 1px solid #e7e7e7;">
                                        <h5 style="font-size: 15px; color:#000; margin: 0 0 10px;">BONNE AVENTURE BOTTLE
                                            REAL ALE 16.90702 X 12</h5>
                                        <p style="color: #9370db; font-size: 14px; margin: 0; line-height: 1.2;">BATCH
                                            # 12345678910 SAP# Metro 123456 Lowbla: 123456 Sobeys: 12345</p>
                                    </td>
                                    <td style=" border-bottom: 1px solid #e7e7e7;">
                                        3
                                    </td>
                                    <td style=" border-bottom: 1px solid #e7e7e7;">
                                        CA$90.00
                                    </td>
                                    <td style=" border-bottom: 1px solid #e7e7e7;">
                                        CA$270.00
                                    </td>
                                </tr>
                                <tr>
                                    <td
                                        style="padding: 15px 20px; text-align: start; border-bottom: 1px solid #e7e7e7;">
                                        <h5 style="font-size: 15px; color:#000; margin: 0 0 10px;">BONNE AVENTURE BOTTLE
                                            REAL ALE 16.90702 X 12</h5>
                                        <p style="color: #9370db; font-size: 14px; margin: 0; line-height: 1.2;">BATCH
                                            # 12345678910 SAP# Metro 123456 Lowbla: 123456 Sobeys: 12345</p>
                                    </td>
                                    <td style=" border-bottom: 1px solid #e7e7e7;">
                                        3
                                    </td>
                                    <td style=" border-bottom: 1px solid #e7e7e7;">
                                        CA$90.00
                                    </td>
                                    <td style=" border-bottom: 1px solid #e7e7e7;">
                                        CA$270.00
                                    </td>
                                </tr>
                                <tr>
                                    <td
                                        style="padding: 15px 20px; text-align: start; border-bottom: 1px solid #e7e7e7;">
                                        <h5 style="font-size: 15px; color:#000; margin: 0 0 10px;">BONNE AVENTURE BOTTLE
                                            REAL ALE 16.90702 X 12</h5>
                                        <p style="color: #9370db; font-size: 14px; margin: 0; line-height: 1.2;">BATCH
                                            # 12345678910 SAP# Metro 123456 Lowbla: 123456 Sobeys: 12345</p>
                                    </td>
                                    <td style=" border-bottom: 1px solid #e7e7e7;">
                                        3
                                    </td>
                                    <td style=" border-bottom: 1px solid #e7e7e7;">
                                        CA$90.00
                                    </td>
                                    <td style=" border-bottom: 1px solid #e7e7e7;">
                                        CA$270.00
                                    </td>
                                </tr>
                                <tr
                                    style="box-shadow: 0px 5px 10px 0px #e1e1e1; border-bottom-left-radius: 20px; border-bottom-right-radius: 20px;">
                                    <td style="padding: 15px 20px; text-align: start;">
                                        <h5 style="font-size: 15px; color:#000; margin: 0 0 10px;">BONNE AVENTURE BOTTLE
                                            REAL ALE 16.90702 X 12</h5>
                                        <p style="color: #9370db; font-size: 14px; margin: 0; line-height: 1.2;">BATCH
                                            # 12345678910 SAP# Metro 123456 Lowbla: 123456 Sobeys: 12345</p>
                                    </td>
                                    <td>
                                        3
                                    </td>
                                    <td>
                                        CA$90.00
                                    </td>
                                    <td>
                                        CA$270.00
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td colspan="3" style="padding-top: 40px; font-size: 18px; font-weight: 500;">
                        <table style="width: 100%; border-spacing: 0;">
                            <tr>
                                <td style="width: 60%;">
                                    <div class="noteBox"
                                        style="padding: 30px 20px; border-radius: 10px; background: #f2f2f2; font-size: 22px; color: #000; height: 260px;">
                                        Note:
                                    </div>
                                </td>
                                <td style="text-align: start; padding: 0 20px;">
                                    <div style="display: table; width: 100%; padding:10px 20px;">
                                        <div style="display: table-cell;">Products (8x)</div>
                                        <div style="text-align: end;display: table-cell;">CA$614.30</div>
                                    </div>
                                    <div
                                        style="display: table; width: 100%; padding:10px 20px 20px; border-bottom: 1px solid #e4e4e4;">
                                        <div style="display: table-cell;">Products (8x)</div>
                                        <div style="display: table-cell;text-align: end;">CA$614.30</div>
                                    </div>
                                    <div style="display: table; width: 100%; padding:15px 20px;">
                                        <div style="display: table-cell; font-weight: 600;">Subtotal</div>
                                        <div style="text-align: end;display: table-cell;  font-weight: 600;">CA$649.10
                                        </div>
                                    </div>
                                    <div style="display: table; width: 100%; padding:15px 20px;">
                                        <div style="display: table-cell; ">GST (5%) <br> CA$614.30
                                        </div>
                                        <div style="text-align: end;display: table-cell;"> CA$30.72
                                        </div>
                                    </div>
                                    <div style="display: table; width: 100%; padding:15px 20px;">
                                        <div style="display: table-cell; ">QST (9.975%} <br /> on CA$614.30
                                        </div>
                                        <div style="text-align: end;display: table-cell;"> CA$86.01
                                        </div>
                                    </div>
                                    <div style="display: table; width: 100%; padding:15px 20px;">
                                        <div style="display: table-cell; font-weight: 600;">Total</div>
                                        <div style="text-align: end;display: table-cell;  font-weight: 600;">CA$741.10
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td colspan="3" style="padding-top: 30px;">
                        <div style="width: 40%; font-size: 18px; font-weight: 600;">Client Signature</div>
                        <div style="width: 40%; height: 60px; border-bottom: 1px solid #000; background: #ffffff;">
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</body>

</html>