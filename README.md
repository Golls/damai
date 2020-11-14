## 設定
* 請在 .env 檔案裡，設定名為 GOOGLE_API_KEY 的變數，該變數內容請填可使用 Google API 的鍵值。
* address.zip 檔案請解壓縮在 storage/app/public/ 底下。

## 使用範例

要匯入某一區段郵遞區號的資料，請輸入：
```sh
http://您啟用此 Laravel 的 IP 位址/importbyzip/某三碼郵遞區號
```

要匯入全部郵遞區號的資料，請輸入：
```sh
http://您啟用此 Laravel 的 IP 位址/importall
```

## 使用範例（與 Google MAP API 一同查詢）

若要在匯入過程裡透過 Google API 取得地址的經緯度內容，請在上段範例的 URL 尾端，補上內容為 **gm** 的參數。

```sh
http://您啟用此 Laravel 的 IP 位址/importbyzip/三碼郵遞區號/gm

http://您啟用此 Laravel 的 IP 位址/importall/gm
```

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
