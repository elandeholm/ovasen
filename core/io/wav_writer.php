<?php namespace ovasen\io;

class WavWriter
{
    private $file;
    private $number_of_channels;
    private $frame_counter;
    private $clipped_counter;
    private $wav_header;

    // We only support signed 16 bit uncompressed WAV:s
    
    const FRAME_MIN = -32768;
    const FRAME_MAX = 32767;
    
    public function __construct($file_name, $no_channels = 1, $translator_fn) {
        $this->file = fopen($file_name, "w");
        $this->translator_fn = $translator_fn;
        $this->clipped_counter = 0;
        $this->wav_header = pack("@44");
        fwrite($this->file, $wav_header);
    }
    
    private function writeFrame($frame) {
        if ($this->translator_fn) {
            $frame = $this->translator_fn($frame);
        }
        
        // hard clip to avoid integer roll over distortion
        
        $word = $frame + 0.5;
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
