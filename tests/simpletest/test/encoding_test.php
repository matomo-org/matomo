<?php
// $Id: encoding_test.php,v 1.17 2007/04/30 23:39:59 lastcraft Exp $
require_once(dirname(__FILE__) . '/../autorun.php');
require_once(dirname(__FILE__) . '/../url.php');
require_once(dirname(__FILE__) . '/../socket.php');

Mock::generate('SimpleSocket');

class TestOfEncodedParts extends UnitTestCase {
    
    function testFormEncodedAsKeyEqualsValue() {
        $pair = new SimpleEncodedPair('a', 'A');
        $this->assertEqual($pair->asRequest(), 'a=A');
    }
    
    function testMimeEncodedAsHeadersAndContent() {
        $pair = new SimpleEncodedPair('a', 'A');
        $this->assertEqual(
                $pair->asMime(),
                "Content-Disposition: form-data; name=\"a\"\r\n\r\nA");
    }
    
    function testAttachmentEncodedAsHeadersWithDispositionAndContent() {
        $part = new SimpleAttachment('a', 'A', 'aaa.txt');
        $this->assertEqual(
                $part->asMime(),
                "Content-Disposition: form-data; name=\"a\"; filename=\"aaa.txt\"\r\n" .
                        "Content-Type: text/plain\r\n\r\nA");
    }
}

class TestOfEncoding extends UnitTestCase {
    var $_content_so_far;
    
    function write($content) {
        $this->_content_so_far .= $content;
    }
    
    function clear() {
        $this->_content_so_far = '';
    }
    
    function assertWritten($encoding, $content, $message = '%s') {
        $this->clear();
        $encoding->writeTo($this);
        $this->assertIdentical($this->_content_so_far, $content, $message);
    }
    
    function testGetEmpty() {
        $encoding = &new SimpleGetEncoding();
        $this->assertIdentical($encoding->getValue('a'), false);
        $this->assertIdentical($encoding->asUrlRequest(), '');
    }
    
    function testPostEmpty() {
        $encoding = &new SimplePostEncoding();
        $this->assertIdentical($encoding->getValue('a'), false);
        $this->assertWritten($encoding, '');
    }
    
    function testPrefilled() {
        $encoding = &new SimplePostEncoding(array('a' => 'aaa'));
        $this->assertIdentical($encoding->getValue('a'), 'aaa');
        $this->assertWritten($encoding, 'a=aaa');
    }
    
    function testPrefilledWithObject() {
        $encoding = &new SimplePostEncoding(new SimpleEncoding(array('a' => 'aaa')));
        $this->assertIdentical($encoding->getValue('a'), 'aaa');
        $this->assertWritten($encoding, 'a=aaa');
    }
    
    function testMultiplePrefilled() {
        $encoding = &new SimplePostEncoding(array('a' => array('a1', 'a2')));
        $this->assertIdentical($encoding->getValue('a'), array('a1', 'a2'));
        $this->assertWritten($encoding, 'a=a1&a=a2');
    }
    
    function testSingleParameter() {
        $encoding = &new SimplePostEncoding();
        $encoding->add('a', 'Hello');
        $this->assertEqual($encoding->getValue('a'), 'Hello');
        $this->assertWritten($encoding, 'a=Hello');
    }
    
    function testFalseParameter() {
        $encoding = &new SimplePostEncoding();
        $encoding->add('a', false);
        $this->assertEqual($encoding->getValue('a'), false);
        $this->assertWritten($encoding, '');
    }
    
    function testUrlEncoding() {
        $encoding = &new SimplePostEncoding();
        $encoding->add('a', 'Hello there!');
        $this->assertWritten($encoding, 'a=Hello+there%21');
    }
    
    function testUrlEncodingOfKey() {
        $encoding = &new SimplePostEncoding();
        $encoding->add('a!', 'Hello');
        $this->assertWritten($encoding, 'a%21=Hello');
    }
    
    function testMultipleParameter() {
        $encoding = &new SimplePostEncoding();
        $encoding->add('a', 'Hello');
        $encoding->add('b', 'Goodbye');
        $this->assertWritten($encoding, 'a=Hello&b=Goodbye');
    }
    
    function testEmptyParameters() {
        $encoding = &new SimplePostEncoding();
        $encoding->add('a', '');
        $encoding->add('b', '');
        $this->assertWritten($encoding, 'a=&b=');
    }
    
    function testRepeatedParameter() {
        $encoding = &new SimplePostEncoding();
        $encoding->add('a', 'Hello');
        $encoding->add('a', 'Goodbye');
        $this->assertIdentical($encoding->getValue('a'), array('Hello', 'Goodbye'));
        $this->assertWritten($encoding, 'a=Hello&a=Goodbye');
    }
    
    function testAddingLists() {
        $encoding = &new SimplePostEncoding();
        $encoding->add('a', array('Hello', 'Goodbye'));
        $this->assertIdentical($encoding->getValue('a'), array('Hello', 'Goodbye'));
        $this->assertWritten($encoding, 'a=Hello&a=Goodbye');
    }
    
    function testMergeInHash() {
        $encoding = &new SimpleGetEncoding(array('a' => 'A1', 'b' => 'B'));
        $encoding->merge(array('a' => 'A2'));
        $this->assertIdentical($encoding->getValue('a'), array('A1', 'A2'));
        $this->assertIdentical($encoding->getValue('b'), 'B');
    }
    
    function testMergeInObject() {
        $encoding = &new SimpleGetEncoding(array('a' => 'A1', 'b' => 'B'));
        $encoding->merge(new SimpleEncoding(array('a' => 'A2')));
        $this->assertIdentical($encoding->getValue('a'), array('A1', 'A2'));
        $this->assertIdentical($encoding->getValue('b'), 'B');
    }
    
    function testPrefilledMultipart() {
        $encoding = &new SimpleMultipartEncoding(array('a' => 'aaa'), 'boundary');
        $this->assertIdentical($encoding->getValue('a'), 'aaa');
        $this->assertwritten($encoding,
                "--boundary\r\n" .
                "Content-Disposition: form-data; name=\"a\"\r\n" .
                "\r\n" .
                "aaa\r\n" .
                "--boundary--\r\n");
    }
    
    function testAttachment() {
        $encoding = &new SimpleMultipartEncoding(array(), 'boundary');
        $encoding->attach('a', 'aaa', 'aaa.txt');
        $this->assertIdentical($encoding->getValue('a'), 'aaa.txt');
        $this->assertwritten($encoding,
                "--boundary\r\n" .
                "Content-Disposition: form-data; name=\"a\"; filename=\"aaa.txt\"\r\n" .
                "Content-Type: text/plain\r\n" .
                "\r\n" .
                "aaa\r\n" .
                "--boundary--\r\n");
    }
}

class TestOfFormHeaders extends UnitTestCase {
    
    function testEmptyEncodingWritesZeroContentLength() {
        $socket = &new MockSimpleSocket();
        $socket->expectArgumentsAt(0, 'write', array("Content-Length: 0\r\n"));
        $socket->expectArgumentsAt(1, 'write', array("Content-Type: application/x-www-form-urlencoded\r\n"));
        $encoding = &new SimplePostEncoding();
        $encoding->writeHeadersTo($socket);
    }
    
    function testEmptyMultipartEncodingWritesEndBoundaryContentLength() {
        $socket = &new MockSimpleSocket();
        $socket->expectArgumentsAt(0, 'write', array("Content-Length: 14\r\n"));
        $socket->expectArgumentsAt(1, 'write', array("Content-Type: multipart/form-data, boundary=boundary\r\n"));
        $encoding = &new SimpleMultipartEncoding(array(), 'boundary');
        $encoding->writeHeadersTo($socket);
    }
}
?>