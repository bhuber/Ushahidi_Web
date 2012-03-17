import httplib
conn = httplib.HTTPConnection("10.10.10.192")

message = "Location Philadelphia"
conn.request("GET", "/smssync?from=1234567890&message=" + message + "&secret=abc")    
#conn.request("GET", "/smssync?from=1234567890&message=abc;7.77;-9.42;My Title2;Zorzor, Liberia;The description of the event;1,3,4&secret=abc")
r1 = conn.getresponse()
print r1.status, r1.reason, r1.read()