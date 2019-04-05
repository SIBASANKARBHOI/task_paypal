<html>
<head>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
    <style>
        html {
            background-color: grey;
        }

        #container {
            background-color: #31293f;
            height: 430px;
            width: 350px;
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            box-shadow: -5px -5px 25px white, 5px 5px 25px white;
        }

        form {
            margin-top: 30px;
        }

        input[type=text] {
            width: 80%;
            padding: 16px 40px;
            margin: 8px 0;
            box-sizing: border-box;
            border: none;
            background-color: #e04816;
            color: white;
            font-size: 25px;
            border-radius: 50px;
        }

        button{
            background-color: #e04816;
            width: 80%;
            border: none;
            color: white;
            padding: 10px 0;
            margin-top: 40px;
            cursor: pointer;
            font-size: 25px;
            border-radius: 50px;
        }

        button:hover {
            filter: brightness(110%)
        }
        .error{
            color:red;
        }
    </style>
</head>
<body>
<div id="container" align="center">
    <h1 style="color: white">LOGIN FORM</h1>
    <form action="/login" method="post">

        <input type="text" name="email" placeholder="Email"><br>
        <a class="error">{{$errors->first('email')}}</a><br>

        <input type="text" name="password" placeholder="Your Password"><br>
        <a class="error">{{$errors->first('password')}}</a><br>

        <button type="submit" class="btn btn-success">Go!</button>
    </form>
</div>
</body>
</html>
