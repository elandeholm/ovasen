<?php namespace ovasen\io;

class WavWriter
{
    private $file;
    private $number_of_channels;
    private $frame_counter;
    private $clipped_counter;
    private $sample_rate;
    private $wav_header;

    // We only support signed 16 bit linear quant PCM WAV:s
    
    const FRAME_MIN = -32768;
    const FRAME_MAX = 32767;
    
    const HEADER_SIZE = 44;

    private function writeHeaderLE16($pos, $word) {
        $wh = &$this->wav_header;
        $wh{$pos} = $word & 255;
        $wh{$pos+1} = ($word >> 8) & 255;
    }
    
    private function writeHeaderLE32($pos, $long) {
        $this->headerWriteLE16($pos, $long & 65535);
        $this->headerWriteLE16($pos+2, ($long >> 16) & 65535);
    }
    
    private function writeHeaderString($pos, $str) {
        $wh = &$this->wav_header;
        for ($i=0; $i<strlen($str); ++$i) {
            $wh{$pos+$i} = $str{$i};
        }
    }

    // this is called last when we know the number of frames written
    
    private function writeHeader() {
        $this->writeHeaderString(0, "RIFF"); // yea baby
        
        $byte_size = self::HEADER_SIZE - 8 + 2 * $this->frame_counter;
        if ($byte_size > 0x7fffffff) {
            throw new WavWriterException(sprintf("WAV byte size too large: %d", $byte_size));
        }
        
        $this->writeHeaderLE32(4, $byte_size); // byte size of all of the data sans 8 bytes

        $this->writeHeaderLE32(8, "WAVE");
        $this->writeHeaderLE32(12, "fmt ");
        $this->writeHeaderLE32(16, 16); // size of this chunk
        $this->writeHeaderLE16(20, 1);
        $this->writeHeaderLE16(22, $this->number_of_channels);
        $this->writeHeaderLE32(24, $this->sample_rate);
        
        $byte_rate = $this->sample_rate * 2 * $this->number_of_channels;
        if ($byte_rate > 0x7fffffff) {
            throw new WavWriterException(sprintf("WAV byte rate too large: %d", $byte_rate));
        }
        
        $this->writeHeaderLE32(28, $this->sample_rate * 2 * $this->number_of_channels);
        $this->writeHeaderLE32(32, 2 * $this->number_of_channels);
        $this->writeHeaderLE16(34, 16);
        
        $this->writeHeaderString(36, "data");
        
        // no need to overflow check this one, if too big we caught that earlier on
        
        $this->writeHeaderString(40, 2 * $this->frame_counter); // size of this chunk
        $where_was_i = fseek($this->file, 0);
        fwrite($this->file, $this->wav_header);
        $where_was_i = fseek($this->file, $where_was_i);
        
        if ($where_was_i !== self::HEADER_SIZE) {
            throw new WavWriterException(sprintf("WAV header not correctly written"));            
        }
    }
    
    public function __construct($file_name, $number_of_channels = 1, $sample_rate = 44100, $translator_fn = null) {
        $this->file = fopen($file_name, "w");
        $this->translator_fn = $translator_fn;
        $this->clipped_counter = 0;
        $this->number_of_channels = $number_of_channels;
        $this->sample_rate = $sample_rate;
        $pack_fmt = sprintf("@%2d", self::HEADER_SIZE);
        $this->wav_header = pack($pack_fmt);
        @fwrite($this->file, $this->wav_header);
    }
    
    private function writeFrame($frame) {
        if ($this->translator_fn) {
            $frame = $this->translator_fn($frame);
        }
        
        // hard clip to avoid integer roll over distortion
        
        $word = $frame + 0.5; // round up
        if ($word < self::FRAME_MIN) {
            $word = self::FRAME_MIN;
            ++$this->clipped_counter;
        }
        else if ($word > self::FRAME_MAX) {
            $word = self::FRAME_MAX;
            ++$this->clipped_counter;
        }
        
        $short = pack("s", $word);
        fwrite($this->file, $short);
        ++$this->frame_counter;
    }

    public function writeChannels($data) {
        if (!(is_array($data) && count($data) === $this->number_of_channels)) {
            throw new WavWriterException("writeChannels: data dimension must equal number of channels");
        }

        // make sure each channel buffer is the same size

        $number_of_frames = -1;
        for ($channel = 0; $channel < $this->no_channels; ++$channel) {
            $channelFrames = $data[$channel];
            if (!is_array($channelFrames)) {
                throw new WavWriterException("writeChannels: vector of channel frames expected");
            }
            if ($number_of_frames === -1) {
                $number_of_frames = count($channelFrames);            
            }
            else {
                if ($number_of_frames !== count($channelFrames)) {
                    throw new WavWriterException("writeChannels: channel frame vectors must have the same dimensions");                    
                }
            }
        }
        // now we now that (1) all the channel frames are vectors, (2) of the same size and (3)
        // $number_of_frames
        
        for ($frame = 0; $frame < $number_of_frames; ++$frame) {
            for ($channel = 0; $channel < $this->number_of_channels; ++$channel) {
                $this->writeFrame($data[$channel][$frame]);
            }
        }
    }
    
    // Pre interleaved or mono write frames function
    
    public function write($data) {
        if (is_array($data)) {
            foreach ($data as $frame) {
                $this->writeFrame($frame);
            }
        }
        else {
            $this->writeFrame($data);
        }
    }
    
    public function close() {
        $this->writeHeader();
        fclose($this->file);
        return array( $this->number_of_frames,  $this->clipped_counter );
    }
}

class WavWriterException extends \Exception { };
